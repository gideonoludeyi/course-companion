<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string code
 * @property string name
 * @property string duration
 * @property ?string isRequiredByMajor the major that this course is required by (eg. COSC)
 * @property ?string concentration
 * @property ?int minimumGrade
 * @property int prereqCreditCount
 * @property int prereqCreditCountMajor
 * @property ?string prereqs
 * @property Collection<int, Student> eligibleConcentrationStudents
 * @property Collection<int, Student> eligibleMajorStudents
 * @property Collection<int, Student> eligibleElectiveStudents
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
