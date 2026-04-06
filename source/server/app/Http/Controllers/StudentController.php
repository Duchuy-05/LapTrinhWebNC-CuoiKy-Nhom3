<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    // 1. Hàm xử lý khi học sinh bấm nút "Đăng ký / Thanh toán"
    public function enroll($courseId)
    {
        $course = Course::findOrFail($courseId);
        $userId = Auth::id();

        // Kiểm tra xem học sinh này đã mua khóa này trước đây chưa?
        $alreadyBought = Order::where('user_id', $userId)->where('course_id', $courseId)->exists();
        
        if ($alreadyBought) {
            return redirect()->route('student.my_learning')->with('info', 'Bạn đã sở hữu khóa học này rồi!');
        }

        // Tạo đơn hàng mới và lưu vào MongoDB
        $order = new Order();
        $order->user_id = $userId;
        $order->course_id = $courseId;
        $order->amount = $course->price;
        $order->payment_method = 'Chuyển khoản hệ thống'; // Sau này tích hợp VNPay thì đổi ở đây
        $order->status = 'completed'; // Set thẳng thành "Thành công" để học sinh vào học luôn
        $order->save();

        // Chuyển hướng sang trang "Khóa học của tôi" kèm thông báo thành công
        return redirect()->route('student.my_learning')->with('success', 'Thanh toán thành công! Chào mừng bạn đến với khóa học: ' . $course->title);
    }

    // 2. Trang "Khóa học của tôi" (Nơi hiển thị các khóa học đã mua)
    public function myLearning()
    {
        // Lấy danh sách các đơn hàng "Thành công" của user đang đăng nhập
        $orders = Order::where('user_id', Auth::id())
                       ->where('status', 'completed')
                       ->with('course') // Nạp luôn thông tin khóa học đi kèm
                       ->get();

        return view('frontend.my_learning', compact('orders'));
    }
}