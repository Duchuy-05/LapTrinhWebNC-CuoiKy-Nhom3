<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $courses = $request->user()->instructedCourses()->orderBy('title')->get();
        $selectedCourse = $request->integer('course_id');

        $enrollments = Enrollment::query()
            ->whereHas('course', function ($query) use ($request, $selectedCourse) {
                $query->where('instructor_id', $request->user()->id)
                    ->when($selectedCourse, fn ($builder) => $builder->where('id', $selectedCourse));
            })
            ->with(['user', 'course'])
            ->latest('enrolled_at')
            ->paginate(12)
            ->withQueryString();

        $studentCount = Enrollment::query()
            ->whereHas('course', function ($query) use ($request) {
                $query->where('instructor_id', $request->user()->id);
            })
            ->distinct('user_id')
            ->count('user_id');

        return view('instructor.students.index', compact('courses', 'selectedCourse', 'enrollments', 'studentCount'));
    }
}