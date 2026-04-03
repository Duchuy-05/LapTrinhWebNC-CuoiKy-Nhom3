<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\LessonProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCategory = (string) $request->string('category');

        $courses = Course::query()
            ->published()
            ->with(['category', 'instructor'])
            ->withCount(['lessons', 'enrollments', 'quizzes'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($selectedCategory !== '', function ($query) use ($selectedCategory) {
                $query->whereHas('category', function ($builder) use ($selectedCategory) {
                    $builder->where('slug', $selectedCategory);
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = CourseCategory::query()
            ->active()
            ->withCount([
                'courses as published_courses_count' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get();

        return view('courses.index', compact('courses', 'categories', 'search', 'selectedCategory'));
    }

    public function show(Request $request, Course $course): View
    {
        abort_unless($course->is_published, 404);

        $course->load([
            'category',
            'instructor',
            'lessons',
            'posts' => fn ($query) => $query->published()->with('author')->latest('published_at'),
            'quizzes' => fn ($query) => $query->published()->with(['lesson'])->withCount('questions')->latest(),
        ])->loadCount('enrollments');

        $enrollment = null;
        $completedLessonIds = collect();

        if ($request->user()) {
            $enrollment = $request->user()
                ->enrollments()
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment) {
                $completedLessonIds = LessonProgress::query()
                    ->where('user_id', $request->user()->id)
                    ->whereIn('lesson_id', $course->lessons->pluck('id'))
                    ->where('is_completed', true)
                    ->pluck('lesson_id');
            }
        }

        $startLesson = $course->lessons->firstWhere('is_preview', true) ?? $course->lessons->first();
        $relatedCourses = Course::query()
            ->published()
            ->where('id', '!=', $course->id)
            ->where('category_id', $course->category_id)
            ->with(['category', 'instructor'])
            ->withCount(['lessons', 'enrollments'])
            ->take(3)
            ->get();

        return view('courses.show', compact('course', 'enrollment', 'completedLessonIds', 'startLesson', 'relatedCourses'));
    }
}