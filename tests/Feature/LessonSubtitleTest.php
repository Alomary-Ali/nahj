<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonSubtitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LessonSubtitleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_lesson_subtitle_relationship(): void
    {
        $lesson = Lesson::firstOrFail();

        $subtitle = LessonSubtitle::create([
            'lesson_id' => $lesson->id,
            'language' => 'ar',
            'label' => 'العربية (Arabic)',
            'file_url' => '/subtitles/sample-ar.vtt',
            'is_default' => true,
        ]);

        $this->assertInstanceOf(Lesson::class, $subtitle->lesson);
        $this->assertEquals($lesson->id, $subtitle->lesson->id);
        $this->assertTrue($lesson->subtitles->contains('id', $subtitle->id));
    }

    public function test_lecture_player_eager_loads_subtitles(): void
    {
        $course = Course::with('modules.lessons')->firstOrFail();
        $lesson = $course->modules->first()->lessons->first();
        $student = User::where('email', 'student@nahj.com')->firstOrFail();

        // Enroll student to allow lecture viewing
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 0,
            'enrolled_at' => now(),
        ]);

        // Ensure clean slate for test subtitles
        $lesson->subtitles()->delete();

        LessonSubtitle::create([
            'lesson_id' => $lesson->id,
            'language' => 'ar',
            'label' => 'العربية',
            'file_url' => '/subtitles/sample-ar.vtt',
            'is_default' => true,
        ]);

        LessonSubtitle::create([
            'lesson_id' => $lesson->id,
            'language' => 'en',
            'label' => 'English',
            'file_url' => '/subtitles/sample-en.vtt',
            'is_default' => false,
        ]);

        $response = $this->actingAs($student)->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->has('currentLesson.subtitles', 2)
            ->where('currentLesson.subtitles.0.language', 'ar')
            ->where('currentLesson.subtitles.0.label', 'العربية')
            ->where('currentLesson.subtitles.1.language', 'en')
            ->where('currentLesson.subtitles.1.label', 'English')
        );
    }

    public function test_sample_vtt_subtitle_files_exist(): void
    {
        $this->assertFileExists(public_path('subtitles/sample-ar.vtt'));
        $this->assertFileExists(public_path('subtitles/sample-en.vtt'));

        $arContent = file_get_contents(public_path('subtitles/sample-ar.vtt'));
        $this->assertStringContainsString('WEBVTT', $arContent);
        $this->assertStringContainsString('منصة نهج', $arContent);

        $enContent = file_get_contents(public_path('subtitles/sample-en.vtt'));
        $this->assertStringContainsString('WEBVTT', $enContent);
        $this->assertStringContainsString('Nahj', $enContent);
    }
}
