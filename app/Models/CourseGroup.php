<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * a utility model for representation a group of courses
 * @property int id
 * @property \Illuminate\Database\Eloquent\Collection<int, Course> courses
 */
class CourseGroup extends Model
{
    use HasFactory;

    protected $table = 'course_groups';

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, table: 'courses_to_course_groups');
    }

    /**
     * @param \Illuminate\Support\Collection<int, Course>|array<int, Course> $courses
     * @return CourseGroup
     */
    public static function for(array|\Illuminate\Support\Collection $courses): CourseGroup
    {
        $group = static::findForCourses($courses);
        if (!is_null($group))
            return $group;
        $group = new CourseGroup();
        $group->save();
        $group->refresh();
        $group->courses()->saveMany($courses);
        $group->refresh();
        return $group;
    }

    /**
     * retrieve a course group that contains exactly the courses provided (if it exists)
     * @param array<int, Course>|\Illuminate\Support\Collection<int, Course> $courses
     * @return ?CourseGroup
     */
    private static function findForCourses(array|\Illuminate\Support\Collection $courses): ?CourseGroup
    {
        $courseIds = collect($courses)->pluck('id');
        return CourseGroup::select('course_groups.*')
            ->join('courses_to_course_groups', 'courses_to_course_groups.course_group_id', '=', 'course_groups.id')
            ->join('courses', 'courses.id', '=', 'courses_to_course_groups.course_id')
            ->whereIn('courses.id', $courseIds)
            ->groupBy('course_groups.id')
            ->havingRaw('COUNT(DISTINCT courses.id) = ?', [$courseIds->count()])
            ->havingRaw('COUNT(DISTINCT CASE WHEN courses.id IN (?) THEN courses.id END) = ?', [$courseIds, $courseIds->count()])
            ->first();
    }
}