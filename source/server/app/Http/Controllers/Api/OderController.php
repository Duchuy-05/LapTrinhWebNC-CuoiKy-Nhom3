<?php

use App\Models\Order;
use App\Models\Course;

class OderController extends Controller
{
    public function myCourses(Request $request)
    {
        $userId = auth()->id();

        // 1. Lấy tất cả course_id từ bảng Order của User này
        $enrolledCourseIds = Order::where('user_id', $userId)
                                  ->pluck('course_id') // Đây là courseGroupId
                                  ->toArray();

        if (empty($enrolledCourseIds)) {
            return response()->json(['data' => []]);
        }

        // 2. Lấy thông tin các khóa học tương ứng
        // Lưu ý: Luôn lấy bản PUBLISHED mới nhất để học viên học
        $courses = Course::whereIn('courseGroupId', $enrolledCourseIds)
                         ->where('status', 'PUBLISHED')
                         ->get();

        return response()->json(['data' => $courses]);
    }

    public function enrollCourse(Request $request, $courseGroupId)
    {
        $userId = auth()->id();

        // 1. Kiểm tra xem học viên đã đăng ký khóa này chưa?
        $isEnrolled = Order::where('user_id', $userId)
                        ->where('course_id', $courseGroupId)
                        ->where('status', 'SUCCESS')
                        ->exists();

        if ($isEnrolled) {
            return response()->json(['message' => 'Bạn đã sở hữu khóa học này rồi!'], 400);
        }

        // 2. Lấy thông tin khóa học (để kiểm tra giá)
        $course = Course::where('courseGroupId', $courseGroupId)->where('status', 'PUBLISHED')->first();
        if (!$course) {
            return response()->json(['message' => 'Khóa học không tồn tại hoặc chưa xuất bản'], 404);
        }

        // 3. Tạo mới bản ghi vào bảng Order
        $order = Order::create([
            'user_id' => $userId,
            'course_id' => $courseGroupId,
            'price_paid' => $course->price ?? 0, // Lưu lại giá tại thời điểm mua
            'payment_method' => 'FREE',          // Tạm thời coi như miễn phí
            'status' => 'SUCCESS',
            'progress' => 0,                     // Vừa đăng ký nên tiến độ là 0%
        ]);

        // 4. (Tùy chọn) Tăng số lượng học viên của khóa học lên 1
        $course->increment('student_count');

        return response()->json([
            'message' => 'Đăng ký khóa học thành công!',
            'data' => $order
        ], 201);
    }
}