<?php

namespace App\Services;

use App\Models\Order;

class CourseAccessService
{
    /**
     * Lọc và khóa nội dung khóa học nếu học viên chưa thanh toán.
     */
    public function getSanitizedCourseData($course, $user)
    {
        // 1. Kiểm tra trạng thái mua hàng của User hiện tại
        $hasPurchased = false;
        if ($user) {
            // Phải dùng courseGroupId (UUID) — KHÔNG dùng $course->id (MongoDB ObjectId)
            // Status phải là 'SUCCESS' — KHÔNG phải 'paid' (đó là status cũ đã bỏ)
            $hasPurchased = Order::where('user_id', $user->id)
                                 ->where('course_id', $course->courseGroupId)
                                 ->where('status', 'SUCCESS')
                                 ->exists();
        }

        // 2. Lấy dữ liệu bài học gốc từ Database
        $courseData = $course->courseData; 

        // 3. Tiến hành "Cắt xén" dữ liệu nhạy cảm
        foreach ($courseData as &$unit) {
            foreach ($unit['items'] as &$item) {
                
                // Bài học sẽ bị khóa vì chưa mua và không cho học thử (isPreview = false)
                $isLocked = !$hasPurchased && empty($item['isPreview']);
                
                // Gắn nhãn để React biết đường hiển thị giao diện khóa
                $item['is_locked'] = $isLocked;

                // QUAN TRỌNG NHẤT: Xóa sạch toàn bộ nội dung (video, quiz) nếu bị khóa
                // Tránh việc học viên soi tab Network (F12) để lấy trộm link video
                if ($isLocked) {
                    $item['blocks'] = []; 
                }
            }
        }

        return $courseData;
    }
}