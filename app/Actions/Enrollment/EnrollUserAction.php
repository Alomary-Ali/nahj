<?php

namespace App\Actions\Enrollment;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class EnrollUserAction
{
    public function execute(User $user, Course $course, float $pricePaid = 0.00, ?DateTimeInterface $expiresAt = null): Enrollment
    {
        return DB::transaction(function () use ($user, $course, $pricePaid, $expiresAt) {
            $existing = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existing) {
                if ($existing->status !== 'active' || $existing->isExpired()) {
                    $existing->update([
                        'status' => 'active',
                        'price_paid' => $pricePaid,
                        'enrolled_at' => now(),
                        'expires_at' => $expiresAt,
                    ]);
                }
                return $existing;
            }

            return Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => 'active',
                'price_paid' => $pricePaid,
                'enrolled_at' => now(),
                'expires_at' => $expiresAt,
            ]);
        });
    }
}
