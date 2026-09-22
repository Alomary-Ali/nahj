<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_submit_review(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        $response = $this->post(route('courses.reviews.store', $course->slug), [
            'rating' => 5,
            'review_text' => 'محتوى رائع جداً',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_unenrolled_user_cannot_submit_review(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'foundations-of-user-experience-ux-design')->firstOrFail();

        // Ensure student is NOT enrolled in UX design
        Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->delete();

        $response = $this->actingAs($student)->post(route('courses.reviews.store', $course->slug), [
            'rating' => 5,
            'review_text' => 'أريد تقييم الكورس بدون تسجيل',
        ]);

        $response->assertStatus(403);
    }

    public function test_enrolled_student_can_submit_review(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        Enrollment::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ], [
            'status' => 'active',
            'price_paid' => 49.00,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)->post(route('courses.reviews.store', $course->slug), [
            'rating' => 5,
            'review_text' => 'شرح المدرب مبسط ودقيق وتطبيقات عملية متقدمة.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_reviews', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'rating' => 5,
            'review_text' => 'شرح المدرب مبسط ودقيق وتطبيقات عملية متقدمة.',
            'is_published' => true,
        ]);
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        Enrollment::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ], [
            'status' => 'active',
            'price_paid' => 49.00,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)->post(route('courses.reviews.store', $course->slug), [
            'rating' => 7,
            'review_text' => 'تقييم غير صحيح',
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_student_can_update_existing_review(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        Enrollment::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ], [
            'status' => 'active',
            'price_paid' => 49.00,
            'enrolled_at' => now(),
        ]);

        // Submit initial review
        $this->actingAs($student)->post(route('courses.reviews.store', $course->slug), [
            'rating' => 4,
            'review_text' => 'جيد جداً',
        ]);

        // Update review
        $response = $this->actingAs($student)->post(route('courses.reviews.store', $course->slug), [
            'rating' => 5,
            'review_text' => 'ممتاز بعد إكمال المشاريع الأخيرة!',
        ]);

        $response->assertRedirect();

        $this->assertEquals(1, CourseReview::where('user_id', $student->id)->where('course_id', $course->id)->count());
        $this->assertDatabaseHas('course_reviews', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'rating' => 5,
            'review_text' => 'ممتاز بعد إكمال المشاريع الأخيرة!',
        ]);
    }

    public function test_student_can_delete_own_review(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        $review = CourseReview::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ], [
            'rating' => 4,
            'review_text' => 'مراجعة مؤقتة',
            'is_published' => true,
        ]);

        $response = $this->actingAs($student)->delete(route('courses.reviews.destroy', [
            'course' => $course->slug,
            'review' => $review->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('course_reviews', ['id' => $review->id]);
    }

    public function test_student_cannot_delete_other_user_review(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $otherStudent = User::factory()->create();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        $review = CourseReview::create([
            'user_id' => $otherStudent->id,
            'course_id' => $course->id,
            'rating' => 5,
            'review_text' => 'مراجعة طالب آخر',
            'is_published' => true,
        ]);

        $response = $this->actingAs($student)->delete(route('courses.reviews.destroy', [
            'course' => $course->slug,
            'review' => $review->id,
        ]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('course_reviews', ['id' => $review->id]);
    }

    public function test_course_page_displays_reviews_and_rating_stats(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        $response = $this->get(route('courses.show', $course->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Show')
            ->has('reviews')
            ->has('ratingStats')
            ->has('ratingStats.average')
            ->has('ratingStats.total_reviews')
            ->has('ratingStats.distribution')
        );
    }

    public function test_instructor_public_profile_page_renders_with_stats_and_courses(): void
    {
        $instructor = User::where('email', 'instructor@coursura.com')->firstOrFail();

        $response = $this->get(route('instructors.show', $instructor->id));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Instructors/Show')
            ->has('instructor')
            ->where('instructor.name', $instructor->name)
            ->has('instructor.stats.courses_count')
            ->has('instructor.stats.students_count')
            ->has('instructor.stats.average_rating')
            ->has('courses')
        );
    }
}
