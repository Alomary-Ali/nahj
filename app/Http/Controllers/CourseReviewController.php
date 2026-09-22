<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    /**
     * Store or update a user's course review.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();

        // Ensure user is enrolled or has administrative privileges
        if (! $user->isEnrolledIn($course) && ! $user->hasRole('admin')) {
            abort(403, 'يجب أن تكون مسجلاً في هذا المقرر لتتمكن من إضافة تقييم.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:2000'],
        ]);

        CourseReview::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text'] ?? null,
                'is_published' => true,
            ]
        );

        return back()->with('success', 'شكراً لك! تم حفظ تقييمك للمساق بنجاح.');
    }

    /**
     * Delete a review.
     */
    public function destroy(Request $request, Course $course, CourseReview $review): RedirectResponse
    {
        $user = $request->user();

        if ($review->user_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'غير مصرح لك بحذف هذا التقييم.');
        }

        $review->delete();

        return back()->with('success', 'تم حذف التقييم بنجاح.');
    }
}
