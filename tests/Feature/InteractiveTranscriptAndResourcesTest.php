<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\LessonSubtitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InteractiveTranscriptAndResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_lesson_resource_relationship_and_ordering(): void
    {
        // Use lesson 2 which has no seeded resources
        $lesson = Lesson::skip(1)->firstOrFail();
        LessonResource::where('lesson_id', $lesson->id)->delete();

        $res1 = LessonResource::create([
            'lesson_id' => $lesson->id,
            'title' => 'Slides PDF',
            'file_path' => '/resources/slides.pdf',
            'file_type' => 'pdf',
            'file_size_human' => '2.5 MB',
            'sort_order' => 2,
        ]);

        $res2 = LessonResource::create([
            'lesson_id' => $lesson->id,
            'title' => 'Project Repository Link',
            'external_url' => 'https://github.com/Alomary-Ali/nahj',
            'file_type' => 'link',
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(Lesson::class, $res1->lesson);
        $this->assertEquals($lesson->id, $res1->lesson->id);

        // Verify ordered by sort_order
        $resources = $lesson->resources;
        $this->assertCount(2, $resources);
        $this->assertEquals($res2->id, $resources->first()->id);
        $this->assertEquals($res1->id, $resources->last()->id);
    }

    public function test_lecture_player_eager_loads_resources_and_subtitles(): void
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

        $initialCount = $lesson->resources()->count();

        LessonResource::create([
            'lesson_id' => $lesson->id,
            'title' => 'Cheat Sheet PDF',
            'file_path' => '/resources/cheatsheet.pdf',
            'file_type' => 'pdf',
            'file_size_human' => '1.1 MB',
            'sort_order' => 99,
        ]);

        $response = $this->actingAs($student)->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->has('currentLesson.subtitles')
            ->has('currentLesson.resources', $initialCount + 1)
        );
    }

    public function test_vtt_subtitle_files_and_content_format(): void
    {
        $this->assertFileExists(public_path('subtitles/sample-ar.vtt'));
        $this->assertFileExists(public_path('subtitles/sample-en.vtt'));

        $arVtt = file_get_contents(public_path('subtitles/sample-ar.vtt'));
        $this->assertStringContainsString('WEBVTT', $arVtt);
        $this->assertStringContainsString('-->', $arVtt);
        $this->assertStringContainsString('منصة نهج', $arVtt);

        $enVtt = file_get_contents(public_path('subtitles/sample-en.vtt'));
        $this->assertStringContainsString('WEBVTT', $enVtt);
        $this->assertStringContainsString('-->', $enVtt);
        $this->assertStringContainsString('Nahj', $enVtt);
    }
}
