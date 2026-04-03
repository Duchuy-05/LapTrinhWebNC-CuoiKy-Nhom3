<?php

namespace App\Http\Controllers\Admin;

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
    public function index(): View
    {
        $lessons = Lesson::query()
            ->with('course')
            ->orderBy('course_id')
            ->orderBy('sort_order')
            ->paginate(12);

        return view('admin.lessons.index', compact('lessons'));
    }

    public function create(): View
    {
        $courses = Course::query()->orderBy('title')->get();

        return view('admin.lessons.create', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
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
            'slug' => Str::slug($validated['slug'] ?: $validated['title'].'-'.$validated['course_id']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'document_url' => $validated['document_url'] ?? null,
            'sort_order' => $validated['sort_order'],
            'duration_minutes' => $validated['duration_minutes'],
            'is_preview' => $request->boolean('is_preview'),
        ]);

        return redirect()->route('admin.lessons.index')->with('status', 'Đã tạo bài học mới.');
    }

    public function edit(Lesson $lesson): View
    {
        $courses = Course::query()->orderBy('title')->get();

        return view('admin.lessons.edit', compact('lesson', 'courses'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
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
            'slug' => Str::slug($validated['slug'] ?: $validated['title'].'-'.$validated['course_id']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'document_url' => $validated['document_url'] ?? null,
            'sort_order' => $validated['sort_order'],
            'duration_minutes' => $validated['duration_minutes'],
            'is_preview' => $request->boolean('is_preview'),
        ]);

        return redirect()->route('admin.lessons.index')->with('status', 'Đã cập nhật bài học.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return redirect()->route('admin.lessons.index')->with('status', 'Đã xóa bài học.');
    }
}