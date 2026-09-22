<?php

namespace App\Http\Controllers;

use App\Actions\Assessments\SubmitQuizAction;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function show(string $courseSlug, int $quizId): Response|RedirectResponse
    {
        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();

        $quiz = Quiz::where('id', $quizId)
            ->where('course_id', $course->id)
            ->with([
                'lesson',
                'questions.options' => function ($q) {
                    $q->select('id', 'question_id', 'option_text', 'sort_order'); // Hide is_correct from frontend!
                },
            ])
            ->firstOrFail();

        // Gatekeeper: Check authentication and enrollment
        $isFreePreview = (bool) ($quiz->lesson?->is_preview ?? false);
        if (! $isFreePreview) {
            if (! auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'يرجى تسجيل الدخول للوصول إلى هذا الاختبار.');
            }

            $user = auth()->user();
            $isAuthorized = $user->isEnrolledIn($course)
                || $user->hasRole('admin')
                || $user->id === $course->instructor_id;

            if (! $isAuthorized) {
                return redirect()->route('courses.checkout.show', $course->slug)
                    ->with('error', 'هذا الاختبار متاح للطلاب المسجلين في المقرر فقط. يرجى إتمام التسجيل للمتابعة.');
            }
        }

        $latestSubmission = null;
        $certificate = null;
        if (auth()->check()) {
            $latestSubmission = QuizSubmission::where('quiz_id', $quiz->id)
                ->where('user_id', auth()->id())
                ->latest()
                ->first();

            $certificate = Certificate::where('user_id', auth()->id())
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

        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'يرجى تسجيل الدخول لتسليم الاختبار.');
        }

        $user = auth()->user();
        $isAuthorized = $user->isEnrolledIn($course)
            || $user->hasRole('admin')
            || $user->id === $course->instructor_id;

        if (! $isAuthorized) {
            return redirect()->route('courses.checkout.show', $course->slug)
                ->with('error', 'يجب أن تكون مسجلاً في المقرر لتسليم هذا الاختبار.');
        }

        $submission = $submitQuizAction->execute($user, $quiz, $request->input('answers'));

        $certificate = Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();

        if ($submission->passed) {
            $message = "تهانينا! لقد اجتزت الاختبار بنسبة {$submission->percentage}%.";
            if ($certificate) {
                $message .= ' تم استيفاء متطلبات المقرر وإصدار شهادتك الرسمية بنجاح.';
            }

            return redirect()->route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id])
                ->with('success', $message);
        }

        return redirect()->route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id])
            ->with('error', "حصلت على نسبة {$submission->percentage}%. نسبة النجاح المطلوبة هي {$quiz->passing_percentage}%. يمكنك المحاولة مرة أخرى.");
    }
}
