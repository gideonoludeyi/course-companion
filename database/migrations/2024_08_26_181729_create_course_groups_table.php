<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('courses_to_course_groups', function (Blueprint $table) {
            $table->foreignId('course_id')
                ->constrained('courses');
            $table->foreignId('course_group_id')
                ->constrained('course_groups');
            $table->timestamps();
            $table->primary(['course_id', 'course_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses_to_course_groups');
        Schema::dropIfExists('course_groups');
    }
};
