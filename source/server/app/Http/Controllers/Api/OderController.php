<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Course;
class OderController extends Controller
{
    public function myCourses(Request $request)
    {
        $userId = auth()->id(); // Hoặc auth('sanctum')->id() tùy cấu hình của bạn

        // 1. Lấy tất cả các đơn hàng (chứa tiến độ) của user này
        $orders = Order::where('user_id', $userId)->get();

        if ($orders->isEmpty()) {
            return response()->json(['data' => []]);
        }

        // Lấy mảng ID khóa học
        $enrolledCourseIds = $orders->pluck('course_id')->toArray();

        // 2. Lấy thông tin chi tiết các khóa học
        $courses = Course::whereIn('courseGroupId', $enrolledCourseIds)
                         ->where('status', 'PUBLISHED')
                         ->get();

        // 3. Gộp dữ liệu: Ghép 'progress' từ bảng Order vào từng Khóa học
        $coursesWithProgress = $courses->map(function($course) use ($orders) {
            // Tìm Order tương ứng với khóa học này
            $order = $orders->firstWhere('course_id', $course->courseGroupId);
            
            // Chuyển object Course thành mảng để dễ thêm dữ liệu
            $courseData = $course->toArray();
            
            // Bơm thêm biến progress vào (nếu không có thì mặc định là 0)
            $courseData['progress'] = $order ? ($order->progress ?? 0) : 0;
            
            return $courseData;
        });

        // Trả về danh sách khóa học ĐÃ CÓ KÈM TIẾN ĐỘ
        return response()->json(['data' => $coursesWithProgress]);
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