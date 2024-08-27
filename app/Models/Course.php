<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int id
 * @property string code
 * @property string name
 * @property string duration
 * @property ?string isRequiredByMajor the major that this course is required by (eg. COSC)
 * @property ?array<int, string> concentration e.g. [ "Software Engineering", "Artificial Intelligence" ]
 * @property ?int minimumGrade
 * @property int prereqCreditCount
 * @property int prereqCreditCountMajor
 * @property ?array<string, string> prereqs e.g. [ "COSC 1P02" => "Introduction to Computer Science" ]
 * @property Collection<int, CourseGroup> prerequisites
 * @property Collection<int, Student> eligibleConcentrationStudents
 * @property Collection<int, Student> eligibleMajorStudents
 * @property Collection<int, Student> eligibleElectiveStudents
 * @property Collection<int, Student> eligibleElectiveMajorStudents
 * @property Collection<int, Student> completedStudents
 */
class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'duration',
        'prereqCreditCount',
        'prereqCreditCountMajor',
        'name',
        'prereqs',
        'concentration',
        'isRequiredByMajor',
        'minimumGrade',
    ];

    protected function casts(): array
    {
        return [
            'prereqs' => 'array',
            'concentration' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $course) {
            DB::transaction(function () use ($course) {
                Course::whereIn('code', array_keys($course->prereqs ?? []))
                    ->each(fn($c) => $course->addPrerequisiteChoices([$c]));
            });
        });
    }

    /**
     * add the courses as prerequisite choices
     * such that only one of the choices is required to satisfy the prerequisite
     *
     * For example, with
     * ```
     * $course->addPrerequisiteChoices([$cosc1p50, $cosc1p71]);
     * ```
     * only one of `$cosc1p50` or `$cosc1p71` is required to satisfy the prerequisite.
     *
     * To require all courses to be required, add them individually
     * ```
     * foreach ($prerequisites as $prereq)
     *     $course->addPrerequisiteChoices([$prereq]);
     * ```
     *
     * @param array<int, Course>|\Illuminate\Support\Collection<int, Course> ...$choices
     * @return void
     */
    private function addPrerequisiteChoices(array|\Illuminate\Support\Collection $choices): void
    {
        DB::transaction(fn() => $this->prerequisites()->save(CourseGroup::for($choices)));
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(CourseGroup::class, table: 'courses_to_prerequisites', relatedPivotKey: 'prerequisite_id');
    }

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * TODO: remove once 'completed_courses_courses' table is gone
     * @deprecated use {@link self::completedStudents()} instead
     */
    public function completedCourses(): BelongsToMany
    {
        return $this->belongsToMany(CompletedCourses::class, 'completed_courses_courses');
    }

    public function completedStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'completed_courses_v2');
    }

    /**
     * TODO: remove once 'eligible_courses_major_courses' table is gone
     * @deprecated use {@link self::eligibleMajorStudents()} instead
     */
    public function EligibleCoursesMajor(): BelongsToMany
    {
        return $this->BelongsToMany(EligibleCoursesMajor::class, 'eligible_courses_major_courses');
    }

    public function eligibleMajorStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'eligible_major_courses');
    }

    /**
     * TODO: remove once 'eligible_courses_concentration_courses' table is gone
     * @deprecated use {@link self::eligibleConcentrationStudents()} instead
     */
    public function EligibleCoursesConcentration(): BelongsToMany
    {
        return $this->BelongsToMany(EligibleCoursesConcentration::class, 'eligible_courses_concentration_courses');
    }

    public function eligibleConcentrationStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'eligible_concentration_courses');
    }

    /**
     * TODO: remove once 'eligible_courses_elective_major_courses' table is gone
     * @deprecated use {@link self::eligibleElectiveMajorStudents()}
     */
    public function EligibleCoursesElectiveMajor(): BelongsToMany
    {
        return $this->BelongsToMany(EligibleCoursesElectiveMajor::class, 'eligible_courses_elective_major_courses');
    }

    public function eligibleElectiveMajorStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'eligible_elective_major_courses');
    }

    /**
     * TODO: remove once 'eligible_courses_elective_major_courses' table is gone
     * @deprecated use {@link self::eligibleElectiveStudents()}
     */
    public function EligibleCoursesElective(): BelongsToMany
    {
        return $this->BelongsToMany(EligibleCoursesElective::class, 'eligible_courses_elective_courses');
    }

    public function eligibleElectiveStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'eligible_elective_courses');
    }

    public function CourseFeedback(): HasMany
    {
        return $this->hasMany(CourseFeedback::class);
    }
}
