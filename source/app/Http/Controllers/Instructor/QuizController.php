<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $courseIds = $request->user()->instructedCourses()->pluck('id');

        $quizzes = Quiz::query()
            ->whereIn('course_id', $courseIds)
            ->with(['course', 'lesson'])
            ->withCount('questions')
            ->latest()
            ->paginate(12);

        return view('instructor.quizzes.index', compact('quizzes'));
    }

    public function create(Request $request): View
    {
        $courses = $request->user()->instructedCourses()->with('lessons')->orderBy('title')->get();

        return view('instructor.quizzes.create', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $courseIds = $request->user()->instructedCourses()->pluck('id')->all();
        $lessonIds = Lesson::query()->whereIn('course_id', $courseIds)->pluck('id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'lesson_id' => ['nullable', Rule::in($lessonIds)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'passing_score' => ['required', 'integer', 'between:1,100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $lessonId = $this->resolveLesson($validated['course_id'], $validated['lesson_id'] ?? null);

        Quiz::query()->create([
            'course_id' => $validated['course_id'],
            'lesson_id' => $lessonId,
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $this->makeSlug($validated['title']),
            'description' => $validated['description'] ?? null,
            'passing_score' => $validated['passing_score'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('instructor.quizzes.index')->with('status', 'Đã tạo bài kiểm tra mới.');
    }

    public function edit(Request $request, Quiz $quiz): View
    {
        $quiz = $this->ownedQuiz($request, $quiz);
        $courses = $request->user()->instructedCourses()->with('lessons')->orderBy('title')->get();

        return view('instructor.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $quiz = $this->ownedQuiz($request, $quiz);
        $courseIds = $request->user()->instructedCourses()->pluck('id')->all();
        $lessonIds = Lesson::query()->whereIn('course_id', $courseIds)->pluck('id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'lesson_id' => ['nullable', Rule::in($lessonIds)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'passing_score' => ['required', 'integer', 'between:1,100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $lessonId = $this->resolveLesson($validated['course_id'], $validated['lesson_id'] ?? null);

        $quiz->update([
            'course_id' => $validated['course_id'],
            'lesson_id' => $lessonId,
            'title' => $validated['title'],
            'slug' => $this->makeSlug($validated['title'], $quiz->id),
            'description' => $validated['description'] ?? null,
            'passing_score' => $validated['passing_score'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('instructor.quizzes.index')->with('status', 'Đã cập nhật bài kiểm tra.');
    }

    public function destroy(Request $request, Quiz $quiz): RedirectResponse
    {
        $quiz = $this->ownedQuiz($request, $quiz);
        $quiz->delete();

        return redirect()->route('instructor.quizzes.index')->with('status', 'Đã xóa bài kiểm tra.');
    }

    private function resolveLesson(int $courseId, ?int $lessonId): ?int
    {
        if (! $lessonId) {
            return null;
        }

        $lesson = Lesson::query()->where('id', $lessonId)->where('course_id', $courseId)->firstOrFail();

        return $lesson->id;
    }

    private function ownedQuiz(Request $request, Quiz $quiz): Quiz
    {
        abort_unless($quiz->course && $quiz->course->instructor_id === $request->user()->id, 403);

        return $quiz;
    }

    private function makeSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base !== '' ? $base : 'bai-kiem-tra';
        $index = 1;

        while (Quiz::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$index;
            $index++;
        }

        return $slug;
    }
}