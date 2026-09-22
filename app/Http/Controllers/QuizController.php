<?php

namespace App\Http\Controllers;

use App\Actions\Assessments\SubmitQuizAction;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function show(string $courseSlug, int $quizId): Response
    {
        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();

        $quiz = Quiz::where('id', $quizId)
            ->where('course_id', $course->id)
            ->with(['questions.options' => function ($q) {
                $q->select('id', 'question_id', 'option_text', 'sort_order'); // Hide is_correct from frontend!
            }])
            ->firstOrFail();

        $latestSubmission = null;
        $certificate = null;
        if (auth()->check()) {
            $latestSubmission = QuizSubmission::where('quiz_id', $quiz->id)
                ->where('user_id', auth()->id())
                ->latest()
                ->first();

            $certificate = \App\Models\Certificate::where('user_id', auth()->id())
                ->where('course_id', $course->id)
                ->first();
        }

        return Inertia::render('Courses/Quiz', [
            'course' => $course,
            'quiz' => $quiz,
            'latestSubmission' => $latestSubmission,
            'certificate' => $certificate,
        ]);
    }

    public function submit(Request $request, string $courseSlug, int $quizId, SubmitQuizAction $submitQuizAction): RedirectResponse
    {
        $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();
        $quiz = Quiz::where('id', $quizId)->where('course_id', $course->id)->firstOrFail();

        $submission = $submitQuizAction->execute(auth()->user(), $quiz, $request->input('answers'));

        if ($submission->passed) {
            return redirect()->route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id])
                ->with('success', "تهانينا! لقد اجتزت الاختبار بنسبة {$submission->percentage}%. تم إصدار شهادتك الرسمية بنجاح.");
        }

        return redirect()->route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id])
            ->with('error', "حصلت على نسبة {$submission->percentage}%. نسبة النجاح المطلوبة هي {$quiz->passing_percentage}%. يمكنك المحاولة مرة أخرى.");
    }
}
