<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_categories_can_be_retrieved_with_courses(): void
    {
        $category = Category::where('slug', 'ai-data-science')->first();

        $this->assertNotNull($category);
        $this->assertTrue($category->is_active);
        $this->assertGreaterThanOrEqual(1, $category->courses()->count());
    }

    public function test_course_has_valid_ulid_and_relationships(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->first();

        $this->assertNotNull($course);
        $this->assertNotEmpty($course->ulid);
        $this->assertEquals(26, strlen($course->ulid));
        $this->assertNotNull($course->instructor);
        $this->assertNotNull($course->category);
        $this->assertGreaterThanOrEqual(2, $course->modules()->count());
        $this->assertGreaterThanOrEqual(3, $course->lessons()->count());
    }

    public function test_instructor_can_create_new_course_with_modules_and_lessons(): void
    {
        $instructor = User::where('email', 'instructor@nahj.com')->first();
        $category = Category::first();

        $course = Course::create([
            'title' => 'Machine Learning Foundations',
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'level' => 'beginner',
            'language' => 'en',
            'price' => 39.99,
            'duration_hours' => 15,
            'is_published' => true,
        ]);

        $this->assertNotNull($course->ulid);
        $this->assertStringContainsString('machine-learning-foundations', $course->slug);

        $module = $course->modules()->create([
            'title' => 'Module 1: Supervised Learning',
            'sort_order' => 1,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Linear Regression',
            'type' => 'video',
            'duration_seconds' => 450,
            'video_url' => 'https://example.com/video.mp4',
            'is_preview' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => 'Machine Learning Foundations']);
        $this->assertDatabaseHas('course_modules', ['id' => $module->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'module_id' => $module->id]);
        $this->assertEquals('07:30', $lesson->formatted_duration);
    }

    public function test_deleting_course_cascades_to_modules_and_lessons(): void
    {
        $course = Course::create([
            'title' => 'Temporary Course',
            'instructor_id' => User::first()->id,
            'category_id' => Category::first()->id,
        ]);

        $module = $course->modules()->create(['title' => 'Temp Module']);
        $lesson = $module->lessons()->create(['title' => 'Temp Lesson']);

        $course->forceDelete();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('course_modules', ['id' => $module->id]);
        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
    }
}
