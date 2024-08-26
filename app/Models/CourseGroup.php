<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int id
 * @property Collection<int, Course> courses
 */
class CourseGroup extends Model
{
    use HasFactory;

    protected $table = 'course_groups';

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, table: 'courses_to_course_groups');
    }
}