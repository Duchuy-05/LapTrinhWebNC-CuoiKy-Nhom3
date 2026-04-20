<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Order;
use App\Services\CourseAccessService;

class LearnController extends Controller
{
    protected $accessService;

    public function __construct(CourseAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    public function showCourseContent($courseGroupId, Request $request)
    {
        $user = $request->user('sanctum'); // null nếu chưa đăng nhập

        $course = Course::where('courseGroupId', $courseGroupId)
                        ->where('status', 'PUBLISHED')
                        ->first();

        if (!$course) {
            return response()->json(['message' => 'Khóa học không tồn tại'], 404);
        }

        // Chưa đăng nhập → chỉ được học thử
        if (!$user) {
            $safeCourseData     = $this->accessService->getSanitizedCourseData($course, null);
            $course->courseData = $safeCourseData;

            return response()->json([
                'data'             => $course,
                'access'           => 'trial',
                'completedLessons' => [],
            ]);
        }

        // Đã đăng nhập → kiểm tra đã mua chưa
        $hasPurchased = Order::where('user_id', $user->id)
                             ->where('course_id', $courseGroupId)
                             ->where('status', 'SUCCESS')
                             ->exists();

        $accessMode         = $hasPurchased ? 'full' : 'trial';
        $safeCourseData     = $this->accessService->getSanitizedCourseData($course, $user);
        $course->courseData = $safeCourseData;

        $order            = Order::where('user_id', $user->id)->where('course_id', $courseGroupId)->first();
        $completedLessons = $order ? ($order->completed_lessons ?? []) : [];

        return response()->json([
            'data'             => $course,
            'access'           => $accessMode,
            'completedLessons' => $completedLessons,
        ]);
    }

    public function updateProgress(Request $request, $courseGroupId)
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để lưu tiến độ'], 401);
        }

        $request->validate(['lessonId' => 'required|string']);
        $lessonId = $request->lessonId;

        // Chỉ học viên đã mua mới được lưu tiến độ
        $order = Order::where('user_id', $user->id)
                      ->where('course_id', $courseGroupId)
                      ->where('status', 'SUCCESS')
                      ->first();

        if (!$order) {
            return response()->json(['message' => 'Bạn chưa sở hữu khóa học này'], 403);
        }

        // Thêm lessonId vào mảng completed_lessons (không trùng)
        $completedLessons = $order->completed_lessons ?? [];
        if (!in_array($lessonId, $completedLessons)) {
            $completedLessons[] = $lessonId;
            $order->update(['completed_lessons' => $completedLessons]);
        }

        // Tính % tiến độ
        $course       = Course::where('courseGroupId', $courseGroupId)->first();
        $totalLessons = collect($course->courseData ?? [])
                            ->flatMap(fn($unit) => $unit['items'] ?? [])
                            ->count();

        $progressPercent = $totalLessons > 0
            ? (int) round((count($completedLessons) / $totalLessons) * 100)
            : 0;

        $order->update(['progress' => $progressPercent]);

        return response()->json([
            'success'          => true,
            'completedLessons' => $completedLessons,
            'progress'         => $progressPercent,
        ]);
    }
}