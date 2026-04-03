<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(Request $request, Course $course, Lesson $lesson): View|RedirectResponse
    {
        abort_unless($course->id === $lesson->course_id, 404);
        abort_unless($course->is_published, 404);

        $course->load(['category', 'instructor', 'lessons']);

        $enrollment = null;
        $completedLessonIds = collect();
        $progressEntry = null;

        if ($request->user()) {
            $enrollment = $request->user()
                ->enrollments()
                ->where('course_id', $course->id)
                ->first();
        }

        if (! $lesson->is_preview && ! $enrollment) {
            return $request->user()
                ? redirect()->route('courses.show', $course)->with('error', 'Bạn cần tham gia khóa học để xem bài học này.')
                : redirect()->route('login')->with('error', 'Hãy đăng nhập để tiếp tục học tập.');
        }

        if ($enrollment) {
            $enrollment->update(['last_accessed_at' => now()]);

            $completedLessonIds = LessonProgress::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('lesson_id', $course->lessons->pluck('id'))
                ->where('is_completed', true)
                ->pluck('lesson_id');

            $progressEntry = LessonProgress::query()
                ->where('user_id', $request->user()->id)
                ->where('lesson_id', $lesson->id)
                ->first();
        }

        $relatedQuizzes = Quiz::query()
            ->published()
            ->where('course_id', $course->id)
            ->where(function ($query) use ($lesson) {
                $query->whereNull('lesson_id')->orWhere('lesson_id', $lesson->id);
            })
            ->withCount('questions')
            ->get();

        $lessons = $course->lessons;
        $currentIndex = $lessons->search(fn (Lesson $item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex !== false && $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;

        return view('lessons.show', compact(
            'course',
            'lesson',
            'enrollment',
            'completedLessonIds',
            'progressEntry',
            'previousLesson',
            'nextLesson',
            'relatedQuizzes'
        ));
    }
}