<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    /**
     * Display public instructor profile with statistics and courses taught.
     */
    public function show(int $id): Response
    {
        $instructor = User::select('id', 'name', 'email', 'created_at')
            ->findOrFail($id);

        $courses = Course::where('instructor_id', $instructor->id)
            ->where('is_published', true)
            ->with(['category:id,name,slug'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->latest()
            ->get();

        $courseIds = $courses->pluck('id');

        $totalStudents = Enrollment::whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        $allReviews = CourseReview::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->get();

        $averageRating = $allReviews->count() > 0 ? round($allReviews->avg('rating'), 1) : 4.9;

        return Inertia::render('Instructors/Show', [
            'instructor' => [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'email' => $instructor->email,
                'joined_at' => $instructor->created_at->format('Y-m-d'),
                'headline' => 'أستاذ ومحاضر معتمد في المنصة الأكاديمية',
                'bio' => 'متخصص في تصميم وتدريس المناهج التقنية والأكاديمية المتقدمة وفق معايير الجامعات العالمية. يقدم مقررات تخصصية تسهم في صقل المهارات الوظيفية وسوق العمل الحديث.',
                'stats' => [
                    'courses_count' => $courses->count(),
                    'students_count' => $totalStudents,
                    'reviews_count' => $allReviews->count(),
                    'average_rating' => $averageRating,
                ],
            ],
            'courses' => $courses,
        ]);
    }
}
