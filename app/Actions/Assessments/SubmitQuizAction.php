<?php

namespace App\Actions\Assessments;

use App\Actions\Curriculum\CheckAndIssueCourseCertificateAction;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubmitQuizAction
{
    public function __construct(
        protected CheckAndIssueCourseCertificateAction $checkAndIssueCertificateAction
    ) {}

    public function execute(User $user, Quiz $quiz, array $submittedAnswers): QuizSubmission
    {
        return DB::transaction(function () use ($user, $quiz, $submittedAnswers) {
            $quiz->load(['questions.options', 'course']);

            $totalPoints = 0;
            $earnedPoints = 0;

            foreach ($quiz->questions as $question) {
                $totalPoints += $question->points;
                $selectedOptionId = $submittedAnswers[$question->id] ?? null;

                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($correctOption && (int) $selectedOptionId === (int) $correctOption->id) {
                    $earnedPoints += $question->points;
                }
            }

            $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
            $passed = $percentage >= (float) $quiz->passing_percentage;

            $attemptCount = QuizSubmission::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->count();

            $submission = QuizSubmission::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'score' => $earnedPoints,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'passed' => $passed,
                'attempt_number' => $attemptCount + 1,
                'answers' => $submittedAnswers,
                'submitted_at' => now(),
            ]);

            // If passed, evaluate course-level completion criteria
            if ($passed && $quiz->course) {
                $this->checkAndIssueCertificateAction->execute($user, $quiz->course);
            }

            return $submission;
        });
    }
}
