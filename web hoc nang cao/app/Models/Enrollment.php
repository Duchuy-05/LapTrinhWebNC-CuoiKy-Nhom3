<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
        'last_accessed_at',
        'progress_percentage',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function syncProgress(): void
    {
        $lessonIds = $this->course->lessons()->pluck('id');
        $totalLessons = $lessonIds->count();

        if ($totalLessons === 0) {
            $this->update([
                'progress_percentage' => 0,
                'completed_at' => null,
            ]);

            return;
        }

        $completedLessons = LessonProgress::query()
            ->where('user_id', $this->user_id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->count();

        $percentage = (int) round(($completedLessons / $totalLessons) * 100);

        $this->update([
            'progress_percentage' => $percentage,
            'completed_at' => $percentage === 100 ? now() : null,
        ]);
    }
}
