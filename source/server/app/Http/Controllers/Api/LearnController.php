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
        $user = $request->user('sanctum');
        
        $course = Course::where('courseGroupId', $courseGroupId)
                        ->where('status', 'PUBLISHED')
                        ->first();

        if (!$course) {
            return response()->json(['message' => 'Khóa học không tồn tại'], 404);
        }

        // 1. Kiểm tra xem user ĐÃ MUA khóa này chưa?
        $hasPurchased = Order::where('user_id', $user->id)
                             ->where('course_id', $courseGroupId)
                             ->where('status', 'SUCCESS')
                             ->exists();

        // Nếu đã mua -> full, Nếu chưa mua -> trial (học thử)
        $accessMode = $hasPurchased ? 'full' : 'trial';

        // 2. Đưa data qua Service gác cổng để khóa bài học & xóa video/quiz lậu
        $safeCourseData = $this->accessService->getSanitizedCourseData($course, $user);
        $course->courseData = $safeCourseData;

        // 3. Lấy tiến độ học tập (Giả định bạn lưu một mảng các bài đã học trong bảng Order)
        $order = Order::where('user_id', $user->id)->where('course_id', $courseGroupId)->first();
        // Nếu bạn có cột completed_lessons kiểu JSON trong bảng orders, nếu không thì để mảng rỗng
        $completedLessons = $order ? ($order->completed_lessons ?? []) : []; 

        return response()->json([
            'data' => $course,
            'access' => $accessMode,
            'completedLessons' => $completedLessons
        ]);
    }
}