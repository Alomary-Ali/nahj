<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionThread extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'user_id',
        'title',
        'body',
        'upvotes_count',
        'replies_count',
        'is_pinned',
        'is_resolved',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_resolved' => 'boolean',
            'upvotes_count' => 'integer',
            'replies_count' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'thread_id')->orderBy('is_instructor_endorsed', 'desc')->orderBy('created_at', 'asc');
    }

    public function isUpvotedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return DiscussionUpvote::where('user_id', $user->id)
            ->where('upvotable_type', 'thread')
            ->where('upvotable_id', $this->id)
            ->exists();
    }
}
