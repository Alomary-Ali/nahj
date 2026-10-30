<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'file_path',
        'external_url',
        'file_type',
        'file_size_human',
        'sort_order',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
