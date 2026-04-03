<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->with(['category', 'instructor'])
            ->withCount(['lessons', 'enrollments'])
            ->latest()
            ->paginate(10);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        $categories = CourseCategory::query()->active()->orderBy('name')->get();
        $instructors = User::query()->whereIn('role', ['admin', 'instructor'])->orderBy('name')->get();

        return view('admin.courses.create', compact('categories', 'instructors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:course_categories,id'],
            'instructor_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'thumbnail' => ['nullable', 'url'],
            'level' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        Course::query()->create([
            'category_id' => $validated['category_id'],
            'instructor_id' => $validated['instructor_id'] ?? null,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug'] ?: $validated['title']),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'thumbnail' => $validated['thumbnail'] ?? null,
            'level' => $validated['level'],
            'duration_minutes' => $validated['duration_minutes'],
            'price' => $validated['price'],
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.courses.index')->with('status', 'Đã tạo khóa học mới.');
    }

    public function edit(Course $course): View
    {
        $categories = CourseCategory::query()->active()->orderBy('name')->get();
        $instructors = User::query()->whereIn('role', ['admin', 'instructor'])->orderBy('name')->get();

        return view('admin.courses.edit', compact('course', 'categories', 'instructors'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:course_categories,id'],
            'instructor_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('courses', 'slug')->ignore($course->id)],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'thumbnail' => ['nullable', 'url'],
            'level' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $course->update([
            'category_id' => $validated['category_id'],
            'instructor_id' => $validated['instructor_id'] ?? null,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug'] ?: $validated['title']),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'thumbnail' => $validated['thumbnail'] ?? null,
            'level' => $validated['level'],
            'duration_minutes' => $validated['duration_minutes'],
            'price' => $validated['price'],
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.courses.index')->with('status', 'Đã cập nhật khóa học.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', 'Đã xóa khóa học.');
    }
}