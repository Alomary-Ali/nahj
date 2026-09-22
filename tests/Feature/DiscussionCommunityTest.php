<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\DiscussionUpvote;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DiscussionCommunityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_course_discussions_index_renders_with_threads_and_filters(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();

        DiscussionThread::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'title' => 'سؤال حول خوارزمية النزول التدريجي',
            'body' => 'كيف نختار معدل التعلم المناسب لتفادي التشتت؟',
        ]);

        $response = $this->get(route('courses.discussions.index', $course->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Discussions/Index')
            ->has('threads.data', 1)
            ->where('threads.data.0.title', 'سؤال حول خوارزمية النزول التدريجي')
            ->has('lessons')
            ->has('filters')
        );
    }

    public function test_authenticated_student_can_create_discussion_thread(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();

        $response = $this->actingAs($student)->post(route('courses.discussions.store', $course->slug), [
            'title' => 'استفسار حول دالة التنشيط ReLU',
            'body' => 'لماذا يفضل استخدام ReLU على السيجمويد في الطبقات المخفية؟',
            'lesson_id' => $lesson->id,
        ]);

        $thread = DiscussionThread::where('title', 'استفسار حول دالة التنشيط ReLU')->first();
        $this->assertNotNull($thread);
        $this->assertEquals($student->id, $thread->user_id);
        $this->assertEquals($lesson->id, $thread->lesson_id);

        $response->assertRedirect(route('courses.discussions.show', ['course' => $course->slug, 'thread' => $thread->id]));
    }

    public function test_authenticated_student_can_reply_to_thread(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();

        $thread = DiscussionThread::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'title' => 'نقاش حول متطلبات بايثون',
            'body' => 'ما هو إصدار بايثون المطلوب لتطبيق الدروس؟',
        ]);

        $response = $this->actingAs($student)->post(route('courses.discussions.reply', [
            'course' => $course->slug,
            'thread' => $thread->id,
        ]), [
            'body' => 'يمكنك استخدام بايثون 3.10 أو أحدث بدون مشاكل.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('discussion_replies', [
            'thread_id' => $thread->id,
            'user_id' => $student->id,
            'body' => 'يمكنك استخدام بايثون 3.10 أو أحدث بدون مشاكل.',
            'is_instructor_endorsed' => false,
        ]);

        $this->assertEquals(1, $thread->fresh()->replies_count);
    }

    public function test_instructor_reply_is_automatically_endorsed(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $instructor = User::where('email', 'instructor@coursura.com')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();

        $thread = DiscussionThread::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'title' => 'استفسار عن المشاريع العملية',
            'body' => 'هل توجد مشاريع برمجية في نهاية هذا المساق؟',
        ]);

        $this->actingAs($instructor)->post(route('courses.discussions.reply', [
            'course' => $course->slug,
            'thread' => $thread->id,
        ]), [
            'body' => 'نعم، ستطبق مشروعاً متكاملاً لبناء شبكة عصبية وتدريبها من الصفر.',
        ]);

        $reply = DiscussionReply::where('thread_id', $thread->id)->first();
        $this->assertNotNull($reply);
        $this->assertTrue((bool) $reply->is_instructor_endorsed);
    }

    public function test_student_can_toggle_upvote_on_thread(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();

        $thread = DiscussionThread::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'title' => 'موضوع مميز للنقاش',
            'body' => 'مقارنة بين خوارزميات التحسين Adam و RMSprop.',
        ]);

        // First upvote
        $this->actingAs($student)->post(route('courses.discussions.upvote', $course->slug), [
            'upvotable_type' => 'thread',
            'upvotable_id' => $thread->id,
        ]);

        $this->assertEquals(1, $thread->fresh()->upvotes_count);
        $this->assertDatabaseHas('discussion_upvotes', [
            'user_id' => $student->id,
            'upvotable_type' => 'thread',
            'upvotable_id' => $thread->id,
        ]);

        // Second upvote toggles off
        $this->actingAs($student)->post(route('courses.discussions.upvote', $course->slug), [
            'upvotable_type' => 'thread',
            'upvotable_id' => $thread->id,
        ]);

        $this->assertEquals(0, $thread->fresh()->upvotes_count);
        $this->assertDatabaseMissing('discussion_upvotes', [
            'user_id' => $student->id,
            'upvotable_type' => 'thread',
            'upvotable_id' => $thread->id,
        ]);
    }

    public function test_instructor_can_toggle_endorsement_on_reply(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $instructor = User::where('email', 'instructor@coursura.com')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();

        $thread = DiscussionThread::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'title' => 'سؤال رياضي',
            'body' => 'كيف نثبت تفاضل دالة السيجمويد؟',
        ]);

        $reply = DiscussionReply::create([
            'thread_id' => $thread->id,
            'user_id' => $student->id,
            'body' => 'تفاضل السيجمويد هو s(x) * (1 - s(x)).',
            'is_instructor_endorsed' => false,
        ]);

        $this->actingAs($instructor)->post(route('courses.discussions.endorse', [
            'course' => $course->slug,
            'reply' => $reply->id,
        ]));

        $this->assertTrue((bool) $reply->fresh()->is_instructor_endorsed);
    }

    public function test_unauthorized_user_cannot_endorse_reply(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();

        $thread = DiscussionThread::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'title' => 'سؤال رياضي',
            'body' => 'سؤال تجريبي',
        ]);

        $reply = DiscussionReply::create([
            'thread_id' => $thread->id,
            'user_id' => $student->id,
            'body' => 'إجابة تجريبية',
            'is_instructor_endorsed' => false,
        ]);

        $response = $this->actingAs($student)->post(route('courses.discussions.endorse', [
            'course' => $course->slug,
            'reply' => $reply->id,
        ]));

        $response->assertStatus(403);
    }

    public function test_lesson_player_loads_discussions_for_current_lesson(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->firstOrFail();
        $student = User::where('email', 'student@coursura.com')->firstOrFail();

        DiscussionThread::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'title' => 'مرحباً بالجميع في الدورة',
            'body' => 'سعيد بالانضمام لهذا المساق ومتحمس للتعلم!',
        ]);

        $response = $this->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->has('discussions', 1)
            ->where('discussions.0.title', 'مرحباً بالجميع في الدورة')
        );
    }
}
