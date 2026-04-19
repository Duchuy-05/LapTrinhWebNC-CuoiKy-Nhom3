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
        $hasPurchased = false;
        if ($user) {
            $hasPurchased = $user->orders()
                                 ->where('course_id', $course->id)
                                 ->where('status', 'paid')
                                 ->exists();
        }

        // 2. KIỂM TRA KHÓA HỌC MIỄN PHÍ (MỚI THÊM)
        // Nếu giá gốc <= 0 HOẶC (có giá giảm và giá giảm <= 0) thì là khóa học Free
        $price = floatval($course->price);
        $discountPrice = (isset($course->discountPrice) && $course->discountPrice !== '') ? floatval($course->discountPrice) : $price;
        $finalPrice = min($price, $discountPrice);
        $isFreeCourse = ($finalPrice <= 0);

        // 3. Lấy dữ liệu bài học gốc từ Database
        $courseData = $course->courseData; 

        // 4. Tiến hành "Cắt xén" dữ liệu
        foreach ($courseData as &$unit) {
            foreach ($unit['items'] as &$item) {
                
                // BÀI HỌC BỊ KHÓA KHI: Chưa mua + Không được học thử + KHÓA HỌC CÓ PHÍ
                $isLocked = !$hasPurchased && empty($item['isPreview']) && !$isFreeCourse;
                
                // Gắn nhãn để React biết đường hiển thị giao diện khóa
                $item['is_locked'] = $isLocked;

                // Xóa sạch toàn bộ nội dung nếu bị khóa
                if ($isLocked) {
                    $item['blocks'] = []; 
                }
            }
        }

        return $courseData;
    }
}