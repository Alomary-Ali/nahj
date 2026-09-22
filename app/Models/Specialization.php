<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Specialization extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'ulid',
        'title',
        'slug',
        'headline',
        'description',
        'thumbnail',
        'instructor_id',
        'category_id',
        'level',
        'is_published',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($specialization) {
            if (empty($specialization->slug)) {
                $specialization->slug = Str::slug($specialization->title) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
