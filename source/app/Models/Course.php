<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'thumbnail',
        'level',
        'duration_minutes',
        'price',
        'is_featured',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CoursePost::class)->latest('published_at')->latest();
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class)->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    public function formattedPrice(): string
    {
        return $this->isFree() ? 'Miễn phí' : number_format((float) $this->price, 0, ',', '.').' VNĐ';
    }
}