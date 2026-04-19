<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * API Thống kê dành cho Giảng viên
     */
    public function getStatistics()
    {
        $authorId = auth()->id();

        // 1. ĐẾM TRẠNG THÁI KHÓA HỌC
        $draftCount = Course::where('authorId', $authorId)->where('status', 'DRAFT')->count();
        $publishedCount = Course::where('authorId', $authorId)->where('status', 'PUBLISHED')->count();
        $unpublishCount = Course::where('authorId', $authorId)->where('status', 'UNPUBLISHED')->count();

        // 2. ĐẾM KHÓA HỌC XUẤT BẢN THEO NGÀY
        $publishedCourses = Course::where('authorId', $authorId)
            ->where('status', 'PUBLISHED')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $dailyData = [];
        foreach ($publishedCourses as $course) {
            $date = Carbon::parse($course->created_at)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d');
            if (!isset($dailyData[$date])) $dailyData[$date] = 0;
            $dailyData[$date]++;
        }
        ksort($dailyData); 
        $dailyPublished = [];
        foreach ($dailyData as $date => $total) {
            $dailyPublished[] = ['date' => $date, 'total' => $total];
        }

        // ==========================================
        // TÍNH TOÁN DOANH THU & HỌC VIÊN
        // ==========================================
        
        // A. Lấy tất cả ID khóa học của giảng viên này
        $lecturerCourseIds = Course::where('authorId', $authorId)->pluck('courseGroupId')->toArray();
        
        // B. Tổng số học viên (Lấy từ cột student_count)
        $totalStudents = Course::where('authorId', $authorId)->sum('student_count');

        // C. Tính doanh thu từ bảng Order
        $orders = Order::whereIn('course_id', $lecturerCourseIds)
                       ->where('status', 'SUCCESS')
                       ->get();

        $totalRevenue = 0;
        $monthlyData = [];

        foreach ($orders as $order) {
            $totalRevenue += $order->price_paid; // Cộng dồn tổng doanh thu
            
            // Lấy năm-tháng để gom nhóm (VD: 2024-05)
            $sortKey = Carbon::parse($order->created_at)->timezone('Asia/Ho_Chi_Minh')->format('Y-m');
            if (!isset($monthlyData[$sortKey])) {
                $monthlyData[$sortKey] = 0;
            }
            $monthlyData[$sortKey] += $order->price_paid;
        }

        // Sắp xếp các tháng theo thứ tự thời gian tăng dần
        ksort($monthlyData);

        $monthlyRevenue = [];
        foreach ($monthlyData as $key => $revenue) {
            // Format lại thành MM/YYYY cho Frontend hiển thị đẹp
            $displayMonth = Carbon::createFromFormat('Y-m', $key)->format('m/Y');
            $monthlyRevenue[] = [
                'month' => $displayMonth,
                'revenue' => $revenue
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status_counts' => [
                    'DRAFT' => $draftCount,
                    'PUBLISHED' => $publishedCount,
                    'UNPUBLISHED' => $unpublishCount,
                ],
                'daily_published' => $dailyPublished,
                'summary' => [
                    'total_students' => $totalStudents,
                    'total_revenue' => $totalRevenue
                ],
                'monthly_revenue' => $monthlyRevenue
            ]
        ]);
    }
}