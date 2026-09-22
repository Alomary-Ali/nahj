<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Enrolled courses with active status
        $enrollments = Enrollment::where('user_id', $user->id)
            ->with([
                'course' => function ($query) {
                    $query->select('id', 'title', 'slug', 'headline', 'thumbnail', 'duration_hours', 'level', 'instructor_id', 'category_id')
                        ->with([
                            'instructor:id,name',
                            'category:id,name',
                            'modules' => fn ($m) => $m->orderBy('sort_order', 'asc')->with([
                                'lessons' => fn ($l) => $l->orderBy('sort_order', 'asc')->select('id', 'module_id', 'title', 'slug', 'type', 'duration_seconds'),
                            ]),
                        ]);
                },
            ])
            ->latest('enrolled_at')
            ->get();

        // Compute real completion progress for each enrolled course
        $userProgress = \App\Models\LessonProgress::where('user_id', $user->id)->get();

        $enrollments->each(function ($enrollment) use ($userProgress) {
            if (! $enrollment->course) {
                $enrollment->progress_percentage = 0;
                $enrollment->completed_lessons_count = 0;
                $enrollment->total_lessons_count = 0;
                return;
            }
            $totalLessons = $enrollment->course->modules->sum(fn ($m) => $m->lessons->count());
            $courseLessonIds = $enrollment->course->modules->flatMap(fn ($m) => $m->lessons)->pluck('id');
            $completedCount = $userProgress->whereIn('lesson_id', $courseLessonIds)->where('is_completed', true)->count();
            $enrollment->progress_percentage = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;
            $enrollment->completed_lessons_count = $completedCount;
            $enrollment->total_lessons_count = $totalLessons;
        });

        // 2. User's Certificates
        $certificates = Certificate::where('user_id', $user->id)
            ->with(['course:id,title,slug,headline', 'instructor:id,name'])
            ->latest('issue_date')
            ->get();

        // 3. Recommended courses to explore (exclude already enrolled courses)
        $enrolledCourseIds = $enrollments->pluck('course_id');
        $exploreCourses = Course::whereNotIn('id', $enrolledCourseIds)
            ->where('is_published', true)
            ->with(['instructor:id,name', 'category:id,name'])
            ->take(3)
            ->get();

        return Inertia::render('Dashboard', [
            'enrollments' => $enrollments,
            'certificates' => $certificates,
            'exploreCourses' => $exploreCourses,
            'stats' => [
                'enrolled_count' => $enrollments->count(),
                'certificates_count' => $certificates->count(),
                'total_learning_hours' => $enrollments->sum(fn ($e) => $e->course?->duration_hours ?? 0),
            ],
        ]);
    }
}
