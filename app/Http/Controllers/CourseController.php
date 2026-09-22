<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function show(string $slug): Response
    {
        $course = Course::where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'instructor:id,name,email',
                'category:id,name,slug',
                'specialization:id,title,slug',
                'modules' => function ($query) {
                    $query->orderBy('sort_order', 'asc')
                        ->with(['lessons' => fn ($q) => $q->orderBy('sort_order', 'asc')]);
                },
            ])
            ->firstOrFail();

        $totalLessons = $course->modules->sum(fn ($m) => $m->lessons->count());
        $totalDurationSeconds = $course->modules->sum(fn ($m) => $m->lessons->sum('duration_seconds'));

        $relatedCourses = Course::where('category_id', $course->category_id)
            ->where('id', '!=', $course->id)
            ->where('is_published', true)
            ->with(['instructor:id,name', 'category:id,name'])
            ->take(3)
            ->get();

        $isEnrolled = false;
        $userReview = null;
        if (auth()->check()) {
            $user = auth()->user();
            $isEnrolled = $user->isEnrolledIn($course)
                || $user->hasRole('admin')
                || $user->id === $course->instructor_id;

            $userReview = \App\Models\CourseReview::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
        }

        $allReviews = \App\Models\CourseReview::where('course_id', $course->id)
            ->where('is_published', true)
            ->get();

        $totalReviews = $allReviews->count();
        $avgRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 4.8;

        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $allReviews->where('rating', $i)->count();
            $ratingDistribution[$i] = [
                'count' => $count,
                'percentage' => $totalReviews > 0 ? round(($count / $totalReviews) * 100) : ($i === 5 ? 85 : ($i === 4 ? 15 : 0)),
            ];
        }

        $publishedReviews = \App\Models\CourseReview::where('course_id', $course->id)
            ->where('is_published', true)
            ->with('user:id,name')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'userReview' => $userReview,
            'ratingStats' => [
                'average' => $avgRating,
                'total_reviews' => $totalReviews,
                'distribution' => $ratingDistribution,
            ],
            'reviews' => $publishedReviews,
            'stats' => [
                'total_lessons' => $totalLessons,
                'total_modules' => $course->modules->count(),
                'total_duration_minutes' => round($totalDurationSeconds / 60),
            ],
            'relatedCourses' => $relatedCourses,
        ]);
    }

    public function learn(string $slug): RedirectResponse
    {
        $course = Course::where('slug', $slug)->firstOrFail();

        $firstModule = $course->modules()->orderBy('sort_order', 'asc')->first();
        if (! $firstModule) {
            return redirect()->route('courses.show', $slug)->with('error', 'المقرر لا يحتوي على وحدات بعد.');
        }

        $firstLesson = $firstModule->lessons()->orderBy('sort_order', 'asc')->first();
        if (! $firstLesson) {
            return redirect()->route('courses.show', $slug)->with('error', 'الوحدة لا تحتوي على دروس بعد.');
        }

        return redirect()->route('courses.lecture', [
            'course' => $course->slug,
            'lesson' => $firstLesson->slug,
        ]);
    }
}
