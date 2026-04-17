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
        $userId = auth('sanctum')->id(); 
        $course = Course::where('courseGroupId', $courseGroupId)->where('status', 'PUBLISHED')->first();

        if (!$course) return response()->json(['message' => 'Không tìm thấy khóa học'], 404);

        $order = null;
        if ($userId) {
            $order = Order::where('user_id', $userId)->where('course_id', $courseGroupId)->first();
        }

        $isFree = ($course->price == 0 || $course->discountPrice == 0);
        $isEnrolled = ($order && $order->status == 'SUCCESS');

        // Lấy danh sách bài đã học từ đơn hàng
        $completedLessons = ($order && isset($order->completed_lessons)) ? $order->completed_lessons : [];

        if ($isEnrolled || $isFree) {
            return response()->json([
                'data' => $course, 
                'access' => 'full',
                'completedLessons' => $completedLessons
            ]);
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

    public function updateProgress(Request $request, $courseGroupId)
    {
        $userId = auth('sanctum')->id();
        $lessonId = $request->input('lessonId');

        if (!$userId) return response()->json(['message' => 'Yêu cầu đăng nhập'], 401);

        // 1. Lấy thông tin khóa học trước để kiểm tra
        $course = Course::where('courseGroupId', $courseGroupId)->where('status', 'PUBLISHED')->first();
        if (!$course) return response()->json(['message' => 'Không tìm thấy khóa học'], 404);

        // 2. Tìm đơn hàng (lịch sử ghi danh)
        $order = Order::where('user_id', $userId)->where('course_id', $courseGroupId)->first();

        // 3. NẾU CHƯA CÓ ĐƠN HÀNG: Xử lý tự động ghi danh cho khóa miễn phí
        if (!$order) {
            $isFree = ($course->price == 0 || $course->discountPrice == 0);
            
            if ($isFree) {
                // Tự động tạo record ghi danh để có chỗ lưu tiến độ
                $order = new Order();
                $order->user_id = $userId;
                $order->course_id = $courseGroupId;
                $order->price_paid = 0;
                $order->payment_method = 'Free';
                $order->status = 'SUCCESS';
                $order->progress = 0;
                $order->completed_lessons = [];
                $order->save();
            } else {
                // Nếu khóa có phí mà chưa mua thì mới block 403
                return response()->json(['message' => 'Bạn chưa đăng ký khóa học này'], 403);
            }
        }

        // 4. Cập nhật mảng bài học đã hoàn thành
        $completed = $order->completed_lessons ?? [];
        if (!in_array($lessonId, $completed)) {
            $completed[] = $lessonId;
            $order->completed_lessons = $completed;
            
            // Tính toán % tiến độ tổng quát
            $totalLessons = 0;
            if (is_iterable($course->courseData)) {
                foreach ($course->courseData as $unit) {
                    if (isset($unit['items']) && is_array($unit['items'])) {
                        $totalLessons += count($unit['items']);
                    }
                }
            }
            
            $order->progress = ($totalLessons > 0) ? round((count($completed) / $totalLessons) * 100) : 0;
            $order->save();
        }

        return response()->json(['success' => true, 'completedLessons' => $completed, 'progress' => $order->progress]);
    }
}