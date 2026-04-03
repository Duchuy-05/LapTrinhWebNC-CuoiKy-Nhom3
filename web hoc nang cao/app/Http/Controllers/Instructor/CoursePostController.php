<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\CoursePost;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CoursePostController extends Controller
{
    public function index(Request $request): View
    {
        $courseIds = $request->user()->instructedCourses()->pluck('id');

        $posts = CoursePost::query()
            ->whereIn('course_id', $courseIds)
            ->with('course')
            ->latest('published_at')
            ->latest()
            ->paginate(12);

        return view('instructor.posts.index', compact('posts'));
    }

    public function create(Request $request): View
    {
        $courses = $request->user()->instructedCourses()->orderBy('title')->get();

        return view('instructor.posts.create', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $courseIds = $request->user()->instructedCourses()->pluck('id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        CoursePost::query()->create([
            'course_id' => $validated['course_id'],
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $this->makeSlug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
        ]);

        return redirect()->route('instructor.posts.index')->with('status', 'Đã đăng bài viết mới.');
    }

    public function edit(Request $request, CoursePost $post): View
    {
        $post = $this->ownedPost($request, $post);
        $courses = $request->user()->instructedCourses()->orderBy('title')->get();

        return view('instructor.posts.edit', compact('post', 'courses'));
    }

    public function update(Request $request, CoursePost $post): RedirectResponse
    {
        $post = $this->ownedPost($request, $post);
        $courseIds = $request->user()->instructedCourses()->pluck('id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $isPublished = $request->boolean('is_published');

        $post->update([
            'course_id' => $validated['course_id'],
            'title' => $validated['title'],
            'slug' => $this->makeSlug($validated['title'], $post->id),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($post->published_at ?? now()) : null,
        ]);

        return redirect()->route('instructor.posts.index')->with('status', 'Đã cập nhật bài viết.');
    }

    public function destroy(Request $request, CoursePost $post): RedirectResponse
    {
        $post = $this->ownedPost($request, $post);
        $post->delete();

        return redirect()->route('instructor.posts.index')->with('status', 'Đã xóa bài viết.');
    }

    private function ownedPost(Request $request, CoursePost $post): CoursePost
    {
        abort_unless($post->course && $post->course->instructor_id === $request->user()->id, 403);

        return $post;
    }

    private function makeSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base !== '' ? $base : 'bai-viet';
        $index = 1;

        while (CoursePost::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$index;
            $index++;
        }

        return $slug;
    }
}