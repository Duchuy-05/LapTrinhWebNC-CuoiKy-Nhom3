<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Thêm bình luận và đánh giá cho khóa học
     */
    public function addCourseReview(Request $request, $courseGroupId)
    {
        // 1. Validate dữ liệu gửi lên
        $request->validate([
            'content' => 'required|string|max:1000',
            'rating'  => 'required|integer|min:1|max:5',
        ]);

        $userId = auth('sanctum')->id();
        $user = auth('sanctum')->user();

        // 2. Kiểm tra xem học viên đã mua khóa học chưa
        $order = Order::where('user_id', $userId)
                      ->where('course_id', $courseGroupId)
                      ->where('status', 'SUCCESS')
                      ->first();

        if (!$order) {
            return response()->json(['message' => 'Bạn chưa đăng ký khóa học này nên không thể đánh giá.'], 403);
        }

        // 3. Lấy thông tin khóa học
        $course = Course::where('courseGroupId', $courseGroupId)->where('status', 'PUBLISHED')->first();
        if (!$course) {
            return response()->json(['message' => 'Không tìm thấy khóa học'], 404);
        }

        // 4. Lấy mảng comments hiện tại (nếu chưa có thì mảng rỗng)
        $comments = $course->comments ?? [];

        // 5. Kiểm tra nếu user đã bình luận rồi thì lấy index để ghi đè
        $existingIndex = -1;
        foreach ($comments as $index => $c) {
            if (isset($c['user_id']) && $c['user_id'] === $userId) {
                $existingIndex = $index;
                break;
            }
        }

        // Tạo cục dữ liệu bình luận mới
        $newComment = [
            'id'         => uniqid('cmt_'), // ID duy nhất cho bình luận
            'user_id'    => $userId,
            'user_name'  => $user->name ?? 'Học viên',
            'avatar'     => 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'HV') . '&background=random',
            'content'    => $request->content,
            'rating'     => (int) $request->rating,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'replies'    => [] // Dành cho tính năng Giảng viên phản hồi sau này
        ];

        // Cập nhật hoặc Thêm mới
        if ($existingIndex !== -1) {
            $comments[$existingIndex] = $newComment;
        } else {
            array_unshift($comments, $newComment); // Đẩy bình luận mới nhất lên đầu
        }

        // 6. Tính toán lại Rating (Chỉ tính những comment có rating > 0)
        $validRatings = array_filter($comments, fn($c) => isset($c['rating']) && $c['rating'] > 0);
        $ratingCount = count($validRatings);
        
        $totalScore = 0;
        foreach ($validRatings as $c) {
            $totalScore += $c['rating'];
        }
        
        $ratingScore = $ratingCount > 0 ? round($totalScore / $ratingCount, 1) : 0;

        // 7. Cập nhật vào DB
        $course->comments = $comments;
        $course->rating_count = $ratingCount;
        $course->rating_score = $ratingScore;
        $course->save();

        return response()->json([
            'message' => 'Cảm ơn bạn đã đánh giá khóa học!',
            'data' => $newComment,
            'course_stats' => [
                'rating_count' => $ratingCount,
                'rating_score' => $ratingScore
            ]
        ], 200);
    }
}