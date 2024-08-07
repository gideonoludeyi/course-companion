<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Rules\uniqueStudentPerUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // view the students created by this user
        return view('students.index', [
            'students' => Student::where('user_id', '=', Auth::id())->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // validate
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => ['required', 'numeric', 'digits:7', new uniqueStudentPerUser],
            'major' => 'required|string|max:4',
            'concentration' => 'string|nullable|max:255',
        ]);

        // create
        $student = $request->user()->students()->create($validated);
        $student->fillChildren();
        $student->refresh(); // force the model to refresh *flips desk*
        return $this->update($request, $student);
    }

    /**
     * find a student for display
     */
    public function findStudent(Request $request): View
    {

        // get the student from database
        $number = $request->input('number');

        // get the student
        $student = Student::where('number', $number)
            ->where('user_id', auth()->user()->getAuthIdentifier())
            ->first();

        // bail out if the student isn't found
        if (is_null($student)) {
            // view the students created by this user
            return view('students.index', [
                'students' => Student::where('user_id', '=', Auth::id())->get(),
            ]);
        }

        // authorization check
        Gate::authorize('view', $student);

        // update computed student properties
        Gate::authorize('update', $student);
//        $this->updateStudent($student);
//        $student->save();

        // return the student view
        return view('students.show', [
            'student' => $student,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        // update computed student properties
        Gate::authorize('view', $student);
        //$this->updateStudent($student);
        //$student->save();

        // view the student
        return view('students.show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        Gate::authorize('update', $student);
        return view('students.edit', [
            'student' => $student,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {

        Gate::authorize('update', $student);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => ['required', 'numeric', 'digits:7'],
            'major' => 'required|string|max:4',
            'concentration' => 'string|nullable|max:255',
        ]);
        $student->update($validated);

        // update computed student properties
        $this->updateStudent($student, $request);
        $student->save();


        return redirect(route('students.show', [
            'student' => $student,
        ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        //
        Gate::authorize('delete', $student);

        $student->delete();

        return redirect(route('students.index'));
    }

    private function updateCreditsCompleted(student $student): void
    {
        // count completed credits
        $creditsCompleted = 0.0;
        $creditsCompletedMajor = 0.0;
        $creditsCompletedFirstYear = 0.0;
        $electivesCompletedSecondYear = 0.0;

        // each course completed
        foreach ($student->completedCoursesV2 as $courseCompleted) {
            $count = 0;

            // half or full credit?
            if (Str::substr($courseCompleted->code, 6, 1) == "P") {
                $count += 0.5;
            } elseif (Str::substr($courseCompleted->code, 6, 1) == "F") {
                $count += 1;
            }

            // count first year credits
            if (Str::substr($courseCompleted->code, 5, 1) == "1") {
                $creditsCompletedFirstYear += $count;
            }

            // major?
            $course = Course::where('code', $courseCompleted->code)->first();

            // do math
            if (!is_null($course)) {
                // major or elective
                if ($course->isRequiredByMajor == $student->major || Str::substr($course->code, 0, 4) == $student->major) {
                    $creditsCompletedMajor += $count;
                } else {
                    // second year+ elective?
                    if (Str::substr($courseCompleted->code, 5, 1) > 1) {
                        $electivesCompletedSecondYear += $count;
                    }
                }
            }
            // regardless, add to total completed
            $creditsCompleted += $count;
        }

        // update student record
        $student->creditsCompleted = $creditsCompleted;
        $student->creditsCompletedMajor = $creditsCompletedMajor;
        $student->creditsCompletedFirstYear = $creditsCompletedFirstYear;
        $student->electivesCompletedSecondYear = $electivesCompletedSecondYear;
        $student->save();
    }

    private function updateEligibleCourses(Student $student): void
    {
        $courses = Course::all();

        // go through each course
        foreach ($courses as $course) {

            // skip if course is already completed
            foreach ($student->completedCoursesV2 as $courseCompleted) {
                if ($course->code == $courseCompleted->code) {
                    continue 2;
                }
            }

            // skip if we don't meet required credit count
            if ($student->creditsCompleted < $course->prereqCreditCount || $student->creditsCompletedMajor < $course->prereqCreditCountMajor) {
                continue;
            }

            // if there are prereqs
            //if ($course->prereqs[0] != "") {  // why is this always an array instead of null like the others? *cries*
            if ($course->prereqs) {

                // go through the required prereqs
                foreach ($course->prereqs as $coursePrereqCode => $coursePrereqName) {

                    // override for a course most people aren't actually required to take
                    if ($coursePrereqCode == "MATH 1P20")
                        continue;

                    // default to not completed
                    $completed = false;

                    // go through each course completed by the student
                    foreach ($student->completedCoursesV2 as $courseCompleted) {

                        // set it to completed if there's a match
                        if ($courseCompleted->code == $coursePrereqCode) {
                            $completed = true;
                            break;
                        }
                    }

                    // if it hasn't been completed, skip this course
                    if (!$completed) {
                        continue 2;
                    }
                }
            }

            // all checks passed, so add it to as eligible
            // does the required major match the student major
            if ($course->isRequiredByMajor == $student->major) {
                $student->markAsEligibleForMajorCourse($course);
            } else {
                // is it a concentration course?
                $concentration = false;

                // loop through each concentration this course is a part of to check
                if (is_array($course->concentration)) {
                    foreach ($course->concentration as $courseConcentration) {
                        if ($student->concentration == $courseConcentration) {
                            $concentration = true;
                        }
                    }
                }

                // mark course as eligible as appropriate
                if ($concentration) {
                    $student->markAsEligibleForConcentrationCourse($course);
                } else {
                    if (substr($course->code, 0, 4) == $student->major) {
                        $student->markAsEligibleForElectiveMajorCourse($course);
                    } else {
                        $student->markAsEligibleForElectiveCourse($course);
                    }
                }
            }
        }
    }

    private function updateStudent(Student $student, $request = null): void
    {

        // update computed student info
        if (!is_null($request)) {
            $this->updateCoursesTaken($request, $student);
        }

        //dd($student, $request);
        $student->refresh(); // force the model to refresh *flips desk*
        $this->updateCreditsCompleted($student);
        $this->updateEligibleCourses($student);
    }

    private function updateCoursesTaken($request, Student $student): void
    {
        // create array from user input for parsing
        $coursesCompleted = explode(", ", $request->get("coursesCompleted"));

        // add each entry in
        foreach ($coursesCompleted as $courseCompleted) {
            $course = Course::where('code', $courseCompleted)->first(); // get course
            if (is_null($course)) {
                continue;
                //2do: no results found. how'd this happen?
                //dd($courseCompleted);
            }

            // add to completed
            $student->markAsComplete($course);

            // remove from prereqs
            $student->markAsNoLongerEligibleForMajorCourse($course);
            $student->markAsNoLongerEligibleForConcentrationCourse($course);
            $student->markAsNoLongerEligibleForElectiveMajorCourse($course);
            // not implemented
            /*
            if(isset($student->eligibleCoursesContext)) {
                $course->eligibleCoursesContext()->detach($student->eligibleCoursesContext);
            }
            */
            $student->markAsNoLongerEligibleForElectiveCourse($course);
        }
    }

    private function relatedCourseRecordExists($sc, $course): bool
    {
        $found = false;
        if (isset($sc->course)) {
            foreach ($sc->course as $savedCourse) {
                if ($savedCourse->code == $course->code) {
                    $found = true;
                    break;
                }
            }
        }
        return $found;
    }

    /**
     * Note: deprecated for concentration, major, and complete records,
     * `createRelatedCourseRecord($student, 'Concentration' | 'Major' | 'Completed', $code)`.
     * Instead, use
     * - `$student->markAsEligibleForConcentrationCourse($course)` or
     * - `$student->markAsEligibleForMajorCourse($course)` or
     * - `$student->markAsComplete($course)`
     */
    private function createRelatedCourseRecord(Student $student, string $type, string $code): void
    {
        $student->fillChildren();
        $sc = null; // student course of this type
        $course = Course::where('code', $code)->first(); // the course
        if ($type == "Completed") {
            /* DEPRECATED: use Student::markAsComplete instead */
            return;
        } elseif ($type == "Major") {
            /* DEPRECATED: use Student::markAsEligibleForMajorCourse instead */
            return;
        } elseif ($type == "Concentration") {
            /* DEPRECATED: use Student::markAsEligibleForConcentrationCourse instead */
            return;
        } elseif ($type == "ElectiveMajor") {
            /* DEPRECATED: use Student::markAsEligibleForElectiveMajorCourse instead */
            return;
        } elseif ($type == "Context") {
            $sc = $student->eligibleCoursesContext;
        } elseif ($type == "Elective") {
            /* DEPRECATED: use Student::markAsEligibleForElectiveCourse instead */
            return;
        }

        // add the course if it hasn't been added already
        if (!$this->relatedCourseRecordExists($sc, $course)) {
            if ($type == "Context") {
                $course->EligibleCoursesContext()->attach($sc);
            }
        }
    }
}
