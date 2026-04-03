<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->is_published, 404);

        $enrollment = Enrollment::query()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'course_id' => $course->id,
            ],
            [
                'enrolled_at' => now(),
                'last_accessed_at' => now(),
                'progress_percentage' => 0,
            ]
        );

        if (! $enrollment->enrolled_at) {
            $enrollment->update(['enrolled_at' => now()]);
        }

        $firstLesson = $course->lessons()->first();

        if ($firstLesson) {
            return redirect()
                ->route('lessons.show', [$course, $firstLesson])
                ->with('status', 'Đăng ký khóa học thành công. Bạn có thể bắt đầu học ngay.');
        }

        return redirect()
            ->route('courses.show', $course)
            ->with('status', 'Đăng ký khóa học thành công.');
    }

    public function completeLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($course->id === $lesson->course_id, 404);

        $enrollment = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        LessonProgress::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        $enrollment->update(['last_accessed_at' => now()]);
        $enrollment->refresh();
        $enrollment->load('course.lessons');
        $enrollment->syncProgress();

        return redirect()
            ->route('lessons.show', [$course, $lesson])
            ->with('status', 'Tiến độ bài học đã được cập nhật.');
    }
}