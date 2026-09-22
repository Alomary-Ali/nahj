<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    public function update(Request $request, string $courseSlug, string $lessonSlug): JsonResponse
    {
        $validated = $request->validate([
            'seconds_watched' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
        ]);

        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();
        $lesson = Lesson::where('slug', $lessonSlug)
            ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
            ->firstOrFail();

        $user = auth()->user();
        $secondsWatched = $validated['seconds_watched'];
        $durationSeconds = $validated['duration_seconds'] ?? $lesson->duration_seconds;

        $progress = LessonProgress::firstOrNew([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        $wasCompleted = (bool) $progress->is_completed;
        $nowCompleted = $wasCompleted || ($durationSeconds > 0 && ($secondsWatched / $durationSeconds) >= 0.9);

        $progress->fill([
            'course_id' => $course->id,
            'seconds_watched' => $secondsWatched,
            'is_completed' => $nowCompleted,
            'last_watched_at' => now(),
            'completed_at' => ($nowCompleted && ! $wasCompleted) ? now() : $progress->completed_at,
        ]);
        $progress->save();

        // Check overall course progress
        $totalLessons = $course->lessons()->count();
        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->count();

        $completionPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0;

        // If all lessons completed, update enrollment status to completed
        if ($completedLessons >= $totalLessons && $totalLessons > 0) {
            Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'seconds_watched' => $progress->seconds_watched,
            'is_completed' => (bool) $progress->is_completed,
            'completion_percentage' => $completionPercentage,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
        ]);
    }
}
