<?php

namespace App\Services;

class CourseAccessService
{
    /**
     * Lọc và khóa nội dung khóa học nếu học viên chưa thanh toán.
     */
    public function getSanitizedCourseData($course, $user)
    {
        // 1. Kiểm tra trạng thái mua hàng của User hiện tại
        // (Giả sử bạn có bảng orders liên kết user_id và course_id)
        $hasPurchased = false;
        if ($user) {
            $hasPurchased = $user->orders()
                                 ->where('course_id', $course->id)
                                 ->where('status', 'paid')
                                 ->exists();
        }

        // 2. Lấy dữ liệu bài học gốc từ Database
        $courseData = $course->courseData; 

        // 3. Tiến hành "Cắt xén" dữ liệu nhạy cảm
        foreach ($courseData as &$unit) {
            foreach ($unit['items'] as &$item) {
                
                // Bài học sẽ bị KHÓA nếu: Chưa mua VÀ Không cho học thử (isPreview = false)
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