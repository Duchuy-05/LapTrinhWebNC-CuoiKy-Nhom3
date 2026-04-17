<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order; 
use Illuminate\Http\Request;

class LearnController extends Controller
{
    public function showCourseContent($courseGroupId)
    {
        // 1. Dùng guard 'sanctum' để nhận diện User. Khách vãng lai sẽ có $userId = null
        $userId = auth('sanctum')->id(); 
        $isEnrolled = false;

        // 2. Lấy thông tin khóa học bản PUBLISHED
        $course = Course::where('courseGroupId', $courseGroupId)
                        ->where('status', 'PUBLISHED')
                        ->first();

        if (!$course) {
            return response()->json(['message' => 'Khóa học không tồn tại hoặc chưa xuất bản'], 404);
        }

        // 3. Nếu đã đăng nhập, kiểm tra xem đã mua chưa
        if ($userId) {
            $isEnrolled = Order::where('user_id', $userId)
                               ->where('course_id', $courseGroupId)
                               ->where('status', 'SUCCESS') // Hoặc 'completed' tùy bạn quy định
                               ->exists();
        }

        $isFree = ($course->price == 0 || $course->discountPrice == 0);

        // 4. Logic phân quyền trả về nội dung
        if ($isEnrolled || $isFree) {
            // (A) Học viên có quyền truy cập toàn bộ (Do đã mua hoặc khóa học Free)
            return response()->json(['data' => $course, 'access' => 'full']);
        } else {
            // (B) Chế độ học thử (Dành cho khách hoặc người chưa mua)
            $trialData = $course->courseData ?? [];
            $allowedLessonIds = [];

            // Lọc các bài học được đánh dấu 'isPreview'
            foreach ($trialData as &$unit) {
                if (isset($unit['items']) && is_array($unit['items'])) {
                    $unit['items'] = array_filter($unit['items'], function($item) use (&$allowedLessonIds) {
                        if (isset($item['isPreview']) && $item['isPreview'] === true) {
                            $allowedLessonIds[] = $item['id'];
                            return true;
                        }
                        return false;
                    });
                    $unit['items'] = array_values($unit['items']); // Sắp xếp lại chỉ số mảng
                }
            }

            // Lọc data Blocks (Nội dung thực tế) chỉ giữ lại bài học được phép học thử
            $trialBlocks = [];
            if (is_array($course->blocks)) {
                $trialBlocks = array_intersect_key($course->blocks, array_flip($allowedLessonIds));
            }

            return response()->json([
                'data' => [
                    'title' => $course->title,
                    'courseData' => $trialData,
                    'blocks' => $trialBlocks,
                    'isTrial' => true
                ],
                'access' => 'trial'
            ]);
        }
    }
}