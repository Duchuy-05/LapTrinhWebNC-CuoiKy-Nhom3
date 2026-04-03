<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CoursePost;
use App\Models\SiteContent;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredCourses = Course::query()
            ->published()
            ->with(['category', 'instructor'])
            ->withCount(['lessons', 'enrollments'])
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = CourseCategory::query()
            ->active()
            ->withCount([
                'courses as published_courses_count' => fn ($query) => $query->published(),
            ])
            ->orderByDesc('published_courses_count')
            ->take(6)
            ->get();

        $announcements = Announcement::query()
            ->active()
            ->latest()
            ->take(3)
            ->get();

        $latestPosts = CoursePost::query()
            ->published()
            ->with(['course', 'author'])
            ->latest('published_at')
            ->take(3)
            ->get();

        $siteContents = SiteContent::query()
            ->published()
            ->whereIn('key', ['guidelines', 'regulations'])
            ->get()
            ->keyBy('key');

        return view('home', compact('featuredCourses', 'categories', 'announcements', 'latestPosts', 'siteContents'));
    }

    public function page(string $key): View
    {
        $page = SiteContent::query()
            ->published()
            ->where('key', $key)
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }
}