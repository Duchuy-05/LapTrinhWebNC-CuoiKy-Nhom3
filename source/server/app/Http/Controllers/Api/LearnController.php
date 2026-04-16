<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LearnController extends Controller
{
    public function showCourseContent($courseGroupId)
    {
        // 1. Kiểm tra xem học viên có quyền truy cập khóa học này không (đã mua chưa?)
        $userId = auth()->id();
        $isEnrolled = Order::where('user_id', $userId)
                           ->where('course_id', $courseGroupId)
                           ->where('status', 'SUCCESS')
                           ->exists();

        // (Tạm thời bạn có thể comment đoạn kiểm tra này nếu muốn test nhanh mà không cần mua)
        /*
        if (!$isEnrolled) {
            return response()->json(['message' => 'Bạn chưa đăng ký khóa học này!'], 403);
        }
        */

        // 2. Lấy phiên bản PUBLISHED mới nhất của khóa học
        $course = Course::where('courseGroupId', $courseGroupId)
                        ->where('status', 'PUBLISHED')
                        ->first();

        if (!$course) {
            return response()->json(['message' => 'Nội dung bài học chưa được xuất bản'], 404);
        }

        return response()->json(['data' => $course]);
    }
}