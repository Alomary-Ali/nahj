<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'certificate_code',
        'user_id',
        'course_id',
        'instructor_id',
        'grade_percentage',
        'issue_date',
        'verification_hash',
    ];

    protected function casts(): array
    {
        return [
            'grade_percentage' => 'decimal:2',
            'issue_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($cert) {
            if (empty($cert->certificate_code)) {
                $cert->certificate_code = 'COUR-' . date('Y') . '-' . strtoupper(Str::random(8));
            }
            if (empty($cert->verification_hash)) {
                $cert->verification_hash = hash('sha256', $cert->certificate_code . '-' . $cert->user_id . '-' . $cert->course_id . '-' . time());
            }
            if (empty($cert->issue_date)) {
                $cert->issue_date = now()->toDateString();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('certificates.verify', $this->certificate_code);
    }
}
