<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LessonProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unauthenticated_user_cannot_save_progress(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        $response = $this->postJson(route('courses.lecture.progress', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]), [
            'seconds_watched' => 60,
            'duration_seconds' => 600,
        ]);

        $response->assertStatus(401);
    }

    public function test_student_can_save_progress_and_update_seconds_watched(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 0,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)->postJson(route('courses.lecture.progress', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]), [
            'seconds_watched' => 120,
            'duration_seconds' => 600,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_completed' => false,
            'seconds_watched' => 120,
        ]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'seconds_watched' => 120,
            'is_completed' => false,
        ]);
    }

    public function test_watching_at_least_90_percent_marks_lesson_as_completed(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 0,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)->postJson(route('courses.lecture.progress', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]), [
            'seconds_watched' => 540,
            'duration_seconds' => 600,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_completed' => true,
        ]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'is_completed' => true,
        ]);

        $progress = LessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson->id)->first();
        $this->assertNotNull($progress->completed_at);
    }

    public function test_rewatching_earlier_segment_preserves_completed_status(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        LessonProgress::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'seconds_watched' => 600,
            'is_completed' => true,
            'completed_at' => now(),
            'last_watched_at' => now(),
        ]);

        // Rewatching at 15 seconds
        $response = $this->actingAs($student)->postJson(route('courses.lecture.progress', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]), [
            'seconds_watched' => 15,
            'duration_seconds' => 600,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_completed' => true,
            'seconds_watched' => 15,
        ]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'seconds_watched' => 15,
            'is_completed' => true,
        ]);
    }

    public function test_completing_all_lessons_marks_enrollment_as_completed(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 0,
            'enrolled_at' => now(),
        ]);

        $courseLessons = $course->modules()->with('lessons')->get()->flatMap(fn ($m) => $m->lessons);
        $totalLessons = $courseLessons->count();
        $this->assertGreaterThan(0, $totalLessons);

        // Mark all except last lesson completed
        for ($i = 0; $i < $totalLessons - 1; $i++) {
            LessonProgress::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'lesson_id' => $courseLessons[$i]->id,
                'seconds_watched' => 600,
                'is_completed' => true,
                'completed_at' => now(),
                'last_watched_at' => now(),
            ]);
        }

        $lastLesson = $courseLessons->last();

        // Complete the final lesson via API
        $response = $this->actingAs($student)->postJson(route('courses.lecture.progress', [
            'course' => $course->slug,
            'lesson' => $lastLesson->slug,
        ]), [
            'seconds_watched' => $lastLesson->duration_seconds,
            'duration_seconds' => $lastLesson->duration_seconds,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_completed' => true,
            'completion_percentage' => 100,
        ]);

        $this->assertEquals('completed', $enrollment->fresh()->status);
    }

    public function test_player_page_loads_current_progress_and_completed_lesson_ids(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 0,
            'enrolled_at' => now(),
        ]);

        LessonProgress::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'seconds_watched' => 180,
            'is_completed' => true,
            'completed_at' => now(),
            'last_watched_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->has('currentProgress')
            ->where('currentProgress.seconds_watched', 180)
            ->where('currentProgress.is_completed', true)
            ->has('completedLessonIds')
            ->has('courseProgressPercentage')
        );
    }

    public function test_dashboard_displays_calculated_course_progress_percentage(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lessons = $course->modules()->with('lessons')->get()->flatMap(fn ($m) => $m->lessons);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 0,
            'enrolled_at' => now(),
        ]);

        // Complete 1 lesson
        LessonProgress::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lessons[0]->id,
            'seconds_watched' => 300,
            'is_completed' => true,
            'completed_at' => now(),
            'last_watched_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('enrollments', 1)
            ->where('enrollments.0.completed_lessons_count', 1)
            ->where('enrollments.0.progress_percentage', fn ($pct) => $pct > 0)
        );
    }
}
