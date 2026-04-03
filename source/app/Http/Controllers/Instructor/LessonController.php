<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    public function index(Request $request): View
    {
        $courseIds = $request->user()->instructedCourses()->pluck('id');
        $selectedCourse = $request->integer('course_id');

        $lessons = Lesson::query()
            ->whereIn('course_id', $courseIds)
            ->with('course')
            ->when($selectedCourse, fn ($query) => $query->where('course_id', $selectedCourse))
            ->orderBy('course_id')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        $courses = $request->user()->instructedCourses()->orderBy('title')->get();

        return view('instructor.lessons.index', compact('lessons', 'courses', 'selectedCourse'));
    }

    public function create(Request $request): View
    {
        $courses = $request->user()->instructedCourses()->orderBy('title')->get();

        return view('instructor.lessons.create', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $courseIds = $request->user()->instructedCourses()->pluck('id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:lessons,slug'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'document_url' => ['nullable', 'url'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
        ]);

        Lesson::query()->create([
            'course_id' => $validated['course_id'],
            'title' => $validated['title'],
            'slug' => $this->makeSlug($validated['slug'] ?: $validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'document_url' => $validated['document_url'] ?? null,
            'sort_order' => $validated['sort_order'],
            'duration_minutes' => $validated['duration_minutes'],
            'is_preview' => $request->boolean('is_preview'),
        ]);

        return redirect()->route('instructor.lessons.index')->with('status', 'Đã tạo bài học mới.');
    }

    public function edit(Request $request, Lesson $lesson): View
    {
        $lesson = $this->ownedLesson($request, $lesson);
        $courses = $request->user()->instructedCourses()->orderBy('title')->get();

        return view('instructor.lessons.edit', compact('lesson', 'courses'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $lesson = $this->ownedLesson($request, $lesson);
        $courseIds = $request->user()->instructedCourses()->pluck('id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('lessons', 'slug')->ignore($lesson->id)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'document_url' => ['nullable', 'url'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
        ]);

        $lesson->update([
            'course_id' => $validated['course_id'],
            'title' => $validated['title'],
            'slug' => $this->makeSlug($validated['slug'] ?: $validated['title'], $lesson->id),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'document_url' => $validated['document_url'] ?? null,
            'sort_order' => $validated['sort_order'],
            'duration_minutes' => $validated['duration_minutes'],
            'is_preview' => $request->boolean('is_preview'),
        ]);

        return redirect()->route('instructor.lessons.index')->with('status', 'Đã cập nhật bài học.');
    }

    public function destroy(Request $request, Lesson $lesson): RedirectResponse
    {
        $lesson = $this->ownedLesson($request, $lesson);
        $lesson->delete();

        return redirect()->route('instructor.lessons.index')->with('status', 'Đã xóa bài học.');
    }

    private function ownedLesson(Request $request, Lesson $lesson): Lesson
    {
        abort_unless($lesson->course && $lesson->course->instructor_id === $request->user()->id, 403);

        return $lesson;
    }

    private function makeSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base !== '' ? $base : 'bai-hoc';
        $index = 1;

        while (Lesson::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$index;
            $index++;
        }

        return $slug;
    }
}