<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Specialization;
use Inertia\Inertia;
use Inertia\Response;

class SpecializationController extends Controller
{
    /**
     * Display a specialization learning path and its sequence of courses.
     */
    public function show(string $slug): Response
    {
        $specialization = Specialization::where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'instructor:id,name,email',
                'category:id,name,slug',
                'courses' => function ($query) {
                    $query->where('is_published', true)
                        ->with(['category:id,name', 'instructor:id,name'])
                        ->withCount(['modules', 'enrollments'])
                        ->withAvg('reviews', 'rating')
                        ->orderBy('id', 'asc');
                },
            ])
            ->firstOrFail();

        $courseIds = $specialization->courses->pluck('id');

        $totalHours = $specialization->courses->sum('duration_hours');
        $totalStudents = Enrollment::whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        return Inertia::render('Specializations/Show', [
            'specialization' => $specialization,
            'stats' => [
                'total_courses' => $specialization->courses->count(),
                'total_hours' => $totalHours,
                'total_students' => $totalStudents,
            ],
        ]);
    }
}
