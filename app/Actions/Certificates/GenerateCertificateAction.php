<?php

namespace App\Actions\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GenerateCertificateAction
{
    public function execute(User $user, Course $course, float $gradePercentage = 100.00): Certificate
    {
        return DB::transaction(function () use ($user, $course, $gradePercentage) {
            $existing = Certificate::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            return Certificate::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'instructor_id' => $course->instructor_id,
                'grade_percentage' => $gradePercentage,
                'issue_date' => now()->toDateString(),
            ]);
        });
    }
}
