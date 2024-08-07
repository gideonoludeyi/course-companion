<?php

use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eligible_elective_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class);
            $table->foreignIdFor(Student::class);
            $table->timestamps();
        });
        DB::statement(<<<SQL
            INSERT INTO eligible_elective_courses (id, course_id, student_id, created_at, updated_at)
            SELECT ECCC.id, ECCC.course_id, ECC.student_id, ECCC.created_at, ECCC.updated_at
            FROM eligible_courses_elective_courses AS ECCC
            INNER JOIN eligible_courses_electives AS ECC
                ON ECC.id = ECCC.eligible_courses_elective_id
        SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(<<<SQL
            INSERT INTO eligible_courses_elective_courses (id, course_id, eligible_courses_elective_id, created_at, updated_at)
                SELECT E.id, E.course_id, ECC.id, E.created_at, E.updated_at
                FROM eligible_elective_courses as E
                INNER JOIN eligible_courses_electives AS ECC
                    ON ECC.student_id = E.student_id
            ON CONFLICT(id) DO NOTHING
        SQL
        );
        Schema::dropIfExists('eligible_elective_courses');
    }
};
