<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::query()->count(),
            'courses' => Course::query()->count(),
            'enrollments' => Enrollment::query()->count(),
            'announcements' => Announcement::query()->active()->count(),
        ];

        $latestEnrollments = Enrollment::query()
            ->with(['user', 'course'])
            ->latest('enrolled_at')
            ->take(6)
            ->get();

        $recentUsers = User::query()
            ->latest()
            ->take(6)
            ->get();

        $latestCourses = Course::query()
            ->with(['category', 'instructor'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'latestEnrollments', 'recentUsers', 'latestCourses'));
    }
}
