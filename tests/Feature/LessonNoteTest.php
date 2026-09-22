<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LessonNoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_create_note(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        $response = $this->post(route('courses.lecture.notes.store', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]), [
            'timestamp_seconds' => 120,
            'note_text' => 'ملاحظة تجريبية للضيف',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_enrolled_student_can_create_timestamped_note(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        Enrollment::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ], [
            'status' => 'active',
            'price_paid' => 49.00,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)->post(route('courses.lecture.notes.store', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]), [
            'timestamp_seconds' => 145,
            'note_text' => 'مفهوم الـ Gradient Descent تم شرحه هنا بدقة فائقة.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lesson_notes', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => 145,
            'note_text' => 'مفهوم الـ Gradient Descent تم شرحه هنا بدقة فائقة.',
        ]);
    }

    public function test_note_requires_valid_text_and_timestamp(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        $response = $this->actingAs($student)->post(route('courses.lecture.notes.store', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]), [
            'timestamp_seconds' => -5,
            'note_text' => '',
        ]);

        $response->assertSessionHasErrors(['timestamp_seconds', 'note_text']);
    }

    public function test_student_can_delete_own_note(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        $note = LessonNote::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => 60,
            'note_text' => 'ملاحظة سيتم حذفها',
        ]);

        $response = $this->actingAs($student)->delete(route('courses.lecture.notes.destroy', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
            'note' => $note->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('lesson_notes', ['id' => $note->id]);
    }

    public function test_student_cannot_delete_another_user_note(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $otherUser = User::factory()->create();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        $note = LessonNote::create([
            'user_id' => $otherUser->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => 80,
            'note_text' => 'ملاحظة طالب آخر',
        ]);

        $response = $this->actingAs($student)->delete(route('courses.lecture.notes.destroy', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
            'note' => $note->id,
        ]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('lesson_notes', ['id' => $note->id]);
    }

    public function test_player_page_loads_notes_ordered_by_timestamp(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        Enrollment::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ], [
            'status' => 'active',
            'price_paid' => 49.00,
            'enrolled_at' => now(),
        ]);

        LessonNote::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => 200,
            'note_text' => 'الملاحظة المتأخرة',
        ]);

        LessonNote::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => 45,
            'note_text' => 'الملاحظة المبكرة',
        ]);

        $response = $this->actingAs($student)->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->has('notes', 2)
            ->where('notes.0.timestamp_seconds', 45)
            ->where('notes.1.timestamp_seconds', 200)
        );
    }
}
