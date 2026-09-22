<?php

namespace App\Actions\Curriculum;

use App\Actions\Certificates\GenerateCertificateAction;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\QuizSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckAndIssueCourseCertificateAction
{
    public function __construct(
        protected GenerateCertificateAction $generateCertificateAction
    ) {}

    /**
     * Verify whether the student has fulfilled all course completion criteria
     * (100% lesson progress + passing all required quizzes), update enrollment,
     * and issue the official certificate if eligible.
     */
    public function execute(User $user, Course $course): ?Certificate
    {
        return DB::transaction(function () use ($user, $course) {
            // 1. Check if user is enrolled
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            // 2. Check total lessons progress
            $totalLessons = $course->lessons()->count();
            if ($totalLessons > 0) {
                $completedLessons = LessonProgress::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('is_completed', true)
                    ->count();

                if ($completedLessons < $totalLessons) {
                    return null; // Lessons are not yet fully completed
                }
            }

            // 3. Check quizzes completion
            $courseQuizzes = $course->quizzes;
            $grade = 100.00;

            if ($courseQuizzes->isNotEmpty()) {
                $quizIds = $courseQuizzes->pluck('id');

                $passedQuizCount = QuizSubmission::where('user_id', $user->id)
                    ->whereIn('quiz_id', $quizIds)
                    ->where('passed', true)
                    ->distinct('quiz_id')
                    ->count('quiz_id');

                if ($passedQuizCount < $courseQuizzes->count()) {
                    return null; // Not all quizzes have been passed yet
                }

                // Compute overall academic grade based on best attempt per quiz
                $bestScores = QuizSubmission::where('user_id', $user->id)
                    ->whereIn('quiz_id', $quizIds)
                    ->where('passed', true)
                    ->selectRaw('quiz_id, MAX(percentage) as best_percentage')
                    ->groupBy('quiz_id')
                    ->pluck('best_percentage');

                if ($bestScores->isNotEmpty()) {
                    $grade = round((float) $bestScores->avg(), 2);
                }
            }

            // 4. Update enrollment status to completed
            if ($enrollment && $enrollment->status !== 'completed') {
                $enrollment->update(['status' => 'completed']);
            }

            // 5. Generate and return the tamper-proof certificate
            return $this->generateCertificateAction->execute($user, $course, $grade);
        });
    }
}
