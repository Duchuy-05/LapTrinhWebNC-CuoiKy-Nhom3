<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = $request->user()
            ->instructedCourses()
            ->with('category')
            ->withCount(['lessons', 'enrollments', 'quizzes', 'posts'])
            ->latest()
            ->paginate(10);

        return view('instructor.courses.index', compact('courses'));
    }

    public function edit(Request $request, Course $course): View
    {
        $course = $this->ownedCourse($request, $course);

        return view('instructor.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $course = $this->ownedCourse($request, $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'thumbnail' => ['nullable', 'url'],
            'level' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $course->update([
            'title' => $validated['title'],
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'thumbnail' => $validated['thumbnail'] ?? null,
            'level' => $validated['level'],
            'duration_minutes' => $validated['duration_minutes'],
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('instructor.courses.index')->with('status', 'Đã cập nhật khóa học thành công.');
    }

    private function ownedCourse(Request $request, Course $course): Course
    {
        abort_unless($course->instructor_id === $request->user()->id, 403);

        return $course;
    }
}