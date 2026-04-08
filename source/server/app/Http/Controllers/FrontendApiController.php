<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order; // Khai báo Model Order để không bị lỗi 500
use Illuminate\Support\Facades\Hash;

class FrontendApiController extends Controller
{
    // 1. ĐĂNG NHẬP
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return response()->json(['success' => true, 'user' => $user])->header('Access-Control-Allow-Origin', '*');
        }
        return response()->json(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!'])->header('Access-Control-Allow-Origin', '*');
    }

    // 2. ĐĂNG KÝ
    public function register(Request $request)
    {
        if(User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email này đã được sử dụng!'])->header('Access-Control-Allow-Origin', '*');
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'user';
        $user->save();
        return response()->json(['success' => true, 'user' => $user])->header('Access-Control-Allow-Origin', '*');
    }

    // 3. LẤY DANH SÁCH KHÓA HỌC ĐÃ MUA
    public function myCourses(Request $request)
    {
        // Lấy các khóa học trạng thái completed
        $purchasedCourseIds = Order::where('user_id', $request->user_id)
                                   ->where('status', 'completed')
                                   ->pluck('course_id');
                                   
        return response()->json(['success' => true, 'purchased_courses' => $purchasedCourseIds])
                         ->header('Access-Control-Allow-Origin', '*');
    }

    // 4. TẠO ĐƠN HÀNG (CHỜ THANH TOÁN QR)
    public function createOrder(Request $request)
    {
        try {
            $order = new Order();
            $order->user_id = $request->user_id;
            $order->course_id = $request->course_id;
            $order->amount = $request->amount;
            $order->payment_method = 'Chuyen Khoan VietQR';
            $order->status = 'pending';
            $order->save();

            return response()->json(['success' => true, 'order_id' => $order->id])->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Exception $e) {
            // NẾU CÓ LỖI: Trả về chính xác lỗi gì thay vì báo 500
            return response()->json(['success' => false, 'message' => $e->getMessage()])->header('Access-Control-Allow-Origin', '*');
        }
    }

    // 5. WEBHOOK NHẬN TIỀN TỪ SEPAY
    // 5. WEBHOOK NHẬN TIỀN TỪ SEPAY
    public function bankingWebhook(Request $request)
    {
        // SỬA Ở ĐÂY: Dùng hàm input() để lấy dữ liệu an toàn, hết gạch đỏ
        $noidung = $request->input('content'); 
        $sotien  = $request->input('transferAmount');

        // Nếu không có nội dung thì báo lỗi để SePay biết
        if (empty($noidung) || empty($sotien)) {
            return response()->json(['success' => false, 'message' => 'Thiếu dữ liệu']);
        }

        // Tìm mã đơn hàng (Ví dụ: THANHTOAN 123)
        preg_match('/THANHTOAN\s+([a-zA-Z0-9]+)/i', $noidung, $matches);
        
        if (!empty($matches[1])) {
            $orderId = $matches[1];
            $order = \App\Models\Order::find($orderId); 

            // Cập nhật trạng thái nếu đủ tiền
            if ($order && $order->status == 'pending' && $sotien >= $order->amount) {
                $order->status = 'completed';
                $order->save();
            }
        }
        
        // Bắt buộc phải trả về true để SePay hiện tích xanh thành công
        return response()->json(['success' => true]); 
    }

    // API KIỂM TRA TRẠNG THÁI ĐƠN HÀNG (Để HTML gọi vào mỗi 3 giây)
    public function checkOrder($id)
    {
        $order = Order::find($id);
        if ($order) {
            return response()->json(['success' => true, 'status' => $order->status])
                             ->header('Access-Control-Allow-Origin', '*');
        }
        return response()->json(['success' => false])->header('Access-Control-Allow-Origin', '*');
    }
}