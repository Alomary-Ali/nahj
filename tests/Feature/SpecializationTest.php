<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SpecializationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_specialization_show_page_renders_with_courses(): void
    {
        $specialization = Specialization::where('slug', 'deep-learning-specialization')->firstOrFail();

        $response = $this->get(route('specializations.show', $specialization->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Specializations/Show')
            ->has('specialization')
            ->where('specialization.slug', $specialization->slug)
            ->where('specialization.title', $specialization->title)
            ->has('specialization.courses')
            ->has('stats')
            ->has('stats.total_courses')
            ->has('stats.total_hours')
            ->has('stats.total_students')
        );
    }

    public function test_unpublished_specialization_returns_404(): void
    {
        $specialization = Specialization::where('slug', 'deep-learning-specialization')->firstOrFail();
        $specialization->update(['is_published' => false]);

        $response = $this->get(route('specializations.show', $specialization->slug));

        $response->assertStatus(404);
    }

    public function test_course_page_displays_specialization_link_when_attached(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $this->assertNotNull($course->specialization_id);

        $response = $this->get(route('courses.show', $course->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Show')
            ->has('course.specialization')
            ->where('course.specialization.slug', 'deep-learning-specialization')
        );
    }
}
