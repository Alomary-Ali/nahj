<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LearnerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_renders_catalog_with_categories_and_courses(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('categories')
            ->has('courses.data')
            ->has('featuredSpecializations')
        );
    }

    public function test_homepage_can_filter_by_category(): void
    {
        $response = $this->get(route('home', ['category' => 'ai-data-science']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('filters.category', 'ai-data-science')
            ->has('courses.data', 1)
        );
    }

    public function test_homepage_can_search_courses(): void
    {
        $response = $this->get(route('home', ['search' => 'الشبكات']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('filters.search', 'الشبكات')
            ->has('courses.data', 1)
        );
    }

    public function test_course_landing_page_renders_with_syllabus(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->first();

        $response = $this->get(route('courses.show', $course->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Show')
            ->has('course.title')
            ->has('course.modules', 2)
            ->has('stats.total_lessons')
        );
    }

    public function test_learn_redirects_to_first_lecture(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->first();

        $response = $this->get(route('courses.learn', $course->slug));

        $response->assertRedirect(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => 'welcome-course-overview',
        ]));
    }

    public function test_player_renders_lecture_and_navigation(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->first();
        $lesson = Lesson::where('slug', 'welcome-course-overview')->first();

        $response = $this->get(route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $lesson->slug,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Player')
            ->where('currentLesson.title', $lesson->title)
            ->where('navigation.current_index', 1)
            ->where('navigation.next_lesson_slug', 'what-is-neural-network')
        );
    }
}
