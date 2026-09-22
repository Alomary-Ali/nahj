<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'thread_id',
        'user_id',
        'body',
        'upvotes_count',
        'is_instructor_endorsed',
    ];

    protected function casts(): array
    {
        return [
            'upvotes_count' => 'integer',
            'is_instructor_endorsed' => 'boolean',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUpvotedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return DiscussionUpvote::where('user_id', $user->id)
            ->where('upvotable_type', 'reply')
            ->where('upvotable_id', $this->id)
            ->exists();
    }
}
