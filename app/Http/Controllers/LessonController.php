<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function show(string $courseSlug, string $lessonSlug): Response|RedirectResponse
    {
        $course = Course::where('slug', $courseSlug)
            ->where('is_published', true)
            ->with([
                'instructor:id,name',
                'modules' => function ($q) {
                    $q->orderBy('sort_order', 'asc')
                        ->with(['lessons' => fn ($l) => $l->orderBy('sort_order', 'asc')]);
                },
            ])
            ->firstOrFail();

        $lesson = Lesson::where('slug', $lessonSlug)
            ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
            ->firstOrFail();

        // Gatekeeper: If lesson is not preview, student must be authenticated and enrolled (or admin/instructor)
        if (! $lesson->is_preview) {
            if (! auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'يرجى تسجيل الدخول للوصول إلى هذا الدرس المخصص للطلاب المسجلين.');
            }

            $user = auth()->user();
            $isAuthorized = $user->isEnrolledIn($course)
                || $user->hasRole('admin')
                || $user->id === $course->instructor_id;

            if (! $isAuthorized) {
                return redirect()->route('courses.checkout.show', $course->slug)
                    ->with('error', 'هذا الدرس متاح للطلاب المسجلين في المقرر. يرجى إتمام التسجيل للوصول لكامل المحتوى.');
            }
        }

        if ($lesson->type === 'quiz') {
            $quiz = \App\Models\Quiz::where('lesson_id', $lesson->id)
                ->orWhere('course_id', $course->id)
                ->first();

            if ($quiz) {
                return redirect()->route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id]);
            }
        }

        // Flatten all lessons in order to calculate next and previous lesson
        $allLessons = $course->modules->flatMap(fn ($module) => $module->lessons)->values();
        $currentIndex = $allLessons->search(fn ($item) => $item->id === $lesson->id);

        $previousLesson = $currentIndex > 0 ? $allLessons->get($currentIndex - 1) : null;
        $nextLesson = $currentIndex < ($allLessons->count() - 1) ? $allLessons->get($currentIndex + 1) : null;

        $discussions = \App\Models\DiscussionThread::where('lesson_id', $lesson->id)
            ->with([
                'user:id,name',
                'replies' => fn ($q) => $q->with('user:id,name')->orderBy('is_instructor_endorsed', 'desc')->take(3),
            ])
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->take(10)
            ->get();

        $currentProgress = null;
        $completedLessonIds = [];
        $courseProgressPercentage = 0;
        $notes = [];

        if (auth()->check()) {
            $userId = auth()->id();
            $userProgress = \App\Models\LessonProgress::where('user_id', $userId)
                ->where('course_id', $course->id)
                ->get();

            $currentProgress = $userProgress->firstWhere('lesson_id', $lesson->id);
            $completedLessonIds = $userProgress->where('is_completed', true)->pluck('lesson_id')->toArray();

            $totalLessons = $allLessons->count();
            if ($totalLessons > 0 && count($completedLessonIds) > 0) {
                $courseProgressPercentage = round((count($completedLessonIds) / $totalLessons) * 100, 1);
            }

            $notes = \App\Models\LessonNote::where('user_id', $userId)
                ->where('lesson_id', $lesson->id)
                ->orderBy('timestamp_seconds', 'asc')
                ->get();
        }

        return Inertia::render('Courses/Player', [
            'course' => $course,
            'currentLesson' => $lesson,
            'discussions' => $discussions,
            'notes' => $notes,
            'currentProgress' => $currentProgress ? [
                'seconds_watched' => $currentProgress->seconds_watched,
                'is_completed' => (bool) $currentProgress->is_completed,
            ] : null,
            'completedLessonIds' => $completedLessonIds,
            'courseProgressPercentage' => $courseProgressPercentage,
            'navigation' => [
                'previous_lesson_slug' => $previousLesson?->slug,
                'next_lesson_slug' => $nextLesson?->slug,
                'current_index' => $currentIndex + 1,
                'total_lessons' => $allLessons->count(),
            ],
        ]);
    }
}
