<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use function Pest\Faker\fake;
use function Pest\Laravel\actingAs;

it('displays all students belonging to a user', function () {
    $user = User::factory()->create();
    /** @var Student $students */
    $students = Student::factory()->for($user)->createMany();

    $response = actingAs($user)
        ->get(route('students.index'));

    $response->assertOk();
    $response->assertViewIs('students.index');
    $response->assertViewHas('students', $students);
});

it('does not display students belonging to another user', function () {
    $user = User::factory()->create();
    $another = User::factory()->create();
    /** @var \Illuminate\Support\Collection<int, Student> $students */
    $_ = Student::factory()->for($another)->createMany();

    $response = actingAs($user)
        ->get(route('students.index'));

    $response->assertOk();
    $response->assertViewIs('students.index');
    expect($response->viewData('students'))->toBeEmpty();
});

it('displays a student with the given id', function () {
    $user = User::factory()->create();
    /** @var Student $student */
    $student = Student::factory()->for($user)->create();

    $response = actingAs($user)
        ->get(route('students.show', $student->id));

    $response->assertOk();
    $response->assertViewIs('students.show');
    $response->assertViewHas('student', $student);
});

it('displays a student with the given student number', function () {
    $user = User::factory()->create();
    /** @var Student $student */
    $student = Student::factory()->for($user)->create();

    $response = actingAs($user)
        ->get(route('students.findStudent', ['number' => $student->number]));

    $response->assertOk();
    $response->assertViewIs('students.show');
    $response->assertViewHas('student', $student);
});

it('creates a student for a user', function () {
    $user = User::factory()->create();

    $data = [
        'name' => fake()->name,
        'number' => fake()->randomNumber(nbDigits: 7, strict: true),
        'major' => 'COSC',
        'concentration' => 'Artificial Intelligence'
    ];
    $response = actingAs($user)
        ->post(route('students.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('students', $data);
});

it('updates student information', function () {
    $user = User::factory()->create();
    /** @var Student $student */
    $student = Student::factory()->for($user)->create();
    $data = [
        'name' => fake()->name,
        'number' => fake()->randomNumber(nbDigits: 7, strict: true),
        'major' => 'MATH',
        'concentration' => 'Applied Mathematics'
    ];

    $response = actingAs($user)
        ->patch(route('students.update', $student), $data);

    $response->assertRedirectToRoute('students.show', $student->id);
    expect($student->refresh())
        ->attributesToArray()->toContain(...$data);
});

it('updates completed courses for student', function () {
    $user = User::factory()->create();
    /** @var Student $student */
    $student = Student::factory()->for($user)->create();
    Course::factory()->createMany([
        ['code' => 'COSC 1P02'],
        ['code' => 'MATH 1P66'],
        ['code' => 'COSC 1P50'],
    ]);

    $response = actingAs($user)
        ->patch(route('students.update', $student), [
            'name' => $student->name,
            'number' => $student->number,
            'major' => $student->major,
            'coursesCompleted' => 'COSC 1P02, MATH 1P66, COSC 1P50'
        ]);

    $response->assertRedirectToRoute('students.show', $student->id);
    expect($student->refresh())
        ->completedCoursesV2->pluck('code')->toContain('COSC 1P02', 'MATH 1P66', 'COSC 1P50');
});

it('does not delete student belonging to another user', function () {
    $user = User::factory()->create();
    $another = User::factory()->create();
    /** @var Student $student */
    $student = Student::factory()->for($another)->create();

    $response = actingAs($user)
        ->delete(route('students.destroy', $student->id));

    $response->assertForbidden();
});

it('deletes student belonging to the user', function () {
    $user = User::factory()->create();
    /** @var Student $student */
    $student = Student::factory()->for($user)->create();

    $response = actingAs($user)
        ->delete(route('students.destroy', $student->id));

    $response->assertRedirectToRoute('students.index');
    $this->assertModelMissing($student);
});