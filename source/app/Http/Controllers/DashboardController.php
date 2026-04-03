<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()->isInstructor()) {
            return redirect()->route('instructor.dashboard');
        }

        $enrollments = $request->user()
            ->enrollments()
            ->with(['course.category', 'course.instructor', 'course.lessons'])
            ->orderByDesc('last_accessed_at')
            ->get();

        $completedCourses = $enrollments->where('progress_percentage', 100)->count();
        $averageProgress = $enrollments->count() > 0 ? (int) round($enrollments->avg('progress_percentage')) : 0;

        return view('dashboard.index', compact('enrollments', 'completedCourses', 'averageProgress'));
    }
}