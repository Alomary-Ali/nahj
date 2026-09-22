<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EnrollmentAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_preview_lesson_is_accessible_by_guest(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $previewLesson = Lesson::where('slug', 'welcome-course-overview')->where('is_preview', true)->firstOrFail();

        $response = $this->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $previewLesson->slug,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->where('currentLesson.slug', $previewLesson->slug)
        );
    }

    public function test_non_preview_lesson_redirects_guest_to_login(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $paidLesson = Lesson::where('slug', 'ai-history-and-evolution')->where('is_preview', false)->firstOrFail();

        $response = $this->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $paidLesson->slug,
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_non_preview_lesson_redirects_unenrolled_student_to_checkout(): void
    {
        $student = User::where('email', 'student@nahj.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $paidLesson = Lesson::where('slug', 'ai-history-and-evolution')->where('is_preview', false)->firstOrFail();

        $response = $this->actingAs($student)->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $paidLesson->slug,
        ]));

        $response->assertRedirect(route('courses.checkout.show', $course->slug));
    }

    public function test_checkout_page_renders_course_details_and_pricing(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        $response = $this->get(route('courses.checkout.show', $course->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Checkout/Show')
            ->where('course.title', $course->title)
            ->where('course.slug', $course->slug)
        );
    }

    public function test_enrolling_student_creates_active_enrollment_and_grants_access(): void
    {
        $student = User::where('email', 'student@nahj.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $paidLesson = Lesson::where('slug', 'ai-history-and-evolution')->where('is_preview', false)->firstOrFail();

        $response = $this->actingAs($student)->post(route('courses.enroll', $course->slug));

        $response->assertRedirect(route('courses.learn', $course->slug));

        // Assert database record
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Student now accesses paid lesson without redirect to checkout
        $lessonResponse = $this->actingAs($student)->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $paidLesson->slug,
        ]));

        $lessonResponse->assertStatus(200);
        $lessonResponse->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->where('currentLesson.slug', $paidLesson->slug)
        );
    }

    public function test_duplicate_enrollment_is_handled_gracefully(): void
    {
        $student = User::where('email', 'student@nahj.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        // First enroll
        $this->actingAs($student)->post(route('courses.enroll', $course->slug));
        // Second enroll
        $this->actingAs($student)->post(route('courses.enroll', $course->slug));

        // Assert only 1 enrollment row exists
        $this->assertEquals(1, Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->count());
    }

    public function test_dashboard_renders_enrolled_courses_and_certificates(): void
    {
        $student = User::where('email', 'student@nahj.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        // Enroll student
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 49.00,
            'enrolled_at' => now(),
        ]);

        // Issue certificate
        Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $course->instructor_id,
            'grade_percentage' => 100.00,
            'issue_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('enrollments', 1)
            ->where('enrollments.0.course.title', $course->title)
            ->has('certificates', 1)
            ->where('stats.enrolled_count', 1)
            ->where('stats.certificates_count', 1)
        );
    }
}
