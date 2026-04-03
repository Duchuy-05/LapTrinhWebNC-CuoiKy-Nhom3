<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\CoursePost;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $courseIds = $user->instructedCourses()->pluck('id');

        $stats = [
            'courses' => $courseIds->count(),
            'students' => Enrollment::query()->whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id'),
            'lessons' => Lesson::query()->whereIn('course_id', $courseIds)->count(),
            'quizzes' => Quiz::query()->whereIn('course_id', $courseIds)->count(),
            'posts' => CoursePost::query()->whereIn('course_id', $courseIds)->count(),
        ];

        $courses = $user->instructedCourses()
            ->withCount(['lessons', 'enrollments', 'quizzes', 'posts'])
            ->latest()
            ->take(6)
            ->get();

        $recentEnrollments = Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->with(['user', 'course'])
            ->latest('enrolled_at')
            ->take(8)
            ->get();

        return view('instructor.dashboard.index', compact('stats', 'courses', 'recentEnrollments'));
    }
}