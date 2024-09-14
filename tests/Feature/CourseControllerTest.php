<?php

use App\Models\Course;
use App\Models\User;
use function Pest\Laravel\{actingAs};


it('displays all courses', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get(route('courses.index'));

    $response->assertOk();
    $response->assertViewIs('courses.index');
    $response->assertViewHas('courses', Course::all());
});

it('displays a course with the given id', function () {
    $user = User::factory()->create();
    /** @var Course $course */
    $course = Course::factory()->create();

    $response = actingAs($user)
        ->get(route('courses.show', $course->id));

    $response->assertOk();
    $response->assertViewIs('courses.show');
    $response->assertViewHas('course', $course);
});

it('displays a course with the given code', function () {
    $user = User::factory()->create();
    /** @var Course $course */
    $course = Course::factory()->create();

    $response = actingAs($user)
        ->get(route('courses.findCourse', ['code' => $course->code]));

    $response->assertOk();
    $response->assertViewIs('courses.show');
    $response->assertViewHas('course', $course);
});

it('fails to create course for non-advisor', function () {
    $user = User::factory()->create([
        'isAdvisor' => false
    ]);
    $data = [
        'code' => 'COSC 1P02',
        'name' => 'Introduction to Computer Science',
        'duration' => 'D2',
    ];

    $response = actingAs($user)
        ->post(route('courses.store'), $data);

    $response->assertForbidden();
});

it('creates a course for advisor', function () {
    $user = User::factory()->create([
        'isAdvisor' => true
    ]);
    $data = [
        'code' => 'COSC 1P02',
        'name' => 'Introduction to Computer Science',
        'duration' => 'D2',
    ];

    $response = actingAs($user)
        ->post(route('courses.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseCount('courses', 1);
    $this->assertDatabaseHas('courses', $data);
});

it('fails to update course created by another user', function () {
    $user = User::factory()->create(['isAdvisor' => false]);
    $course = Course::factory()
        ->for(User::factory()->create(['isAdvisor' => true]))
        ->create([
            'code' => 'COSC 1P02',
            'name' => 'Introduction to Computer Science',
            'duration' => 'D2',
        ]);

    $response = actingAs($user)
        ->patch(route('courses.update', $course), [
            'name' => 'Intro to CS',
            'code' => 'COSC 1P02',
            'duration' => 'D2',
        ]);

    $response->assertForbidden();
});

it('updates course for advisor', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'isAdvisor' => true
    ]);
    $course = Course::factory()->for($user)->create([
        'code' => 'COSC 1P02',
        'name' => 'Introduction to Computer Science',
        'duration' => 'D2',
    ]);

    $response = actingAs($user)
        ->patch(route('courses.update', $course), [
            'name' => 'Intro to CS',
            'code' => 'COSC 1P02',
            'duration' => 'D2',
        ]);

    $response->assertRedirectToRoute('courses.index');
});

it('deletes a course with the given id', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $course = Course::factory()->for($user)->create([
        'code' => 'COSC 1P02',
        'name' => 'Introduction to Computer Science',
        'duration' => 'D2',
    ]);

    $response = actingAs($user)
        ->delete(route('courses.destroy', $course));

    $response->assertRedirectToRoute('courses.index');
    $this->assertModelMissing($course);
});