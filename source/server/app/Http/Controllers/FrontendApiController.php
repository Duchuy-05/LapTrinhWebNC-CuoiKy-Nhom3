<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order; 
use App\Models\PayoutRequest; // Khai báo thêm PayoutRequest
use Illuminate\Support\Facades\Hash;

class FrontendApiController extends Controller
{
    // 1. ĐĂNG NHẬP
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            
            // SỬA Ở ĐÂY: Dùng lệnh chuẩn của Laravel để cấp Token thật
            $token = $user->createToken('auth_token')->plainTextToken; 

            return response()->json([
                'success' => true, 
                'user' => $user,
                'token' => $token 
            ])->header('Access-Control-Allow-Origin', '*');
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
        
        // SỬA Ở ĐÂY: Dùng lệnh chuẩn của Laravel để cấp Token thật cho user mới
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true, 
            'user' => $user,
            'token' => $token
        ])->header('Access-Control-Allow-Origin', '*');
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

    // 6. API KIỂM TRA TRẠNG THÁI ĐƠN HÀNG (Để HTML gọi vào mỗi 3 giây)
    public function checkOrder($id)
    {
        $order = Order::find($id);
        if ($order) {
            return response()->json(['success' => true, 'status' => $order->status])
                             ->header('Access-Control-Allow-Origin', '*');
        }
        return response()->json(['success' => false])->header('Access-Control-Allow-Origin', '*');
    }

    // ========================================================
    // 7. API XỬ LÝ YÊU CẦU RÚT TIỀN TỪ GIẢNG VIÊN (TỪ REACT)
    // ========================================================
    public function requestPayout(Request $request)
    {
        try {
            $bankInfo = $request->input('bankInfo');
            $amount = $request->input('amount');
            $userId = $request->input('user_id'); // LẤY CHUẨN ID CỦA NGƯỜI ĐANG ĐĂNG NHẬP

            \App\Models\PayoutRequest::create([
                'user_id' => $userId,
                'amount' => $amount,
                'bank_name' => $bankInfo['bankName'],
                'account_name' => $bankInfo['accountName'],
                'account_number' => $bankInfo['accountNumber'],
                'status' => 'pending'
            ]);

            return response()->json(['status' => 'success', 'message' => 'Đã gửi yêu cầu rút tiền thành công']);
            
        } catch (\Throwable $e) { 
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ========================================================
    // 8. API LẤY LỊCH SỬ RÚT TIỀN ĐỂ TRỪ SỐ DƯ (TỪ REACT)
    // ========================================================
    public function myPayouts(Request $request)
    {
        try {
            // Thêm dòng này để kiểm tra xem có nhận được user_id từ React không
            $userId = $request->input('user_id'); 
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Thiếu ID người dùng']);
            }
            
            // Ép kiểu ID về String nếu cần (MongoDB đôi khi kén kiểu dữ liệu)
            $payouts = \App\Models\PayoutRequest::where('user_id', (string)$userId)
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                                    
            return response()->json(['success' => true, 'payouts' => $payouts]);
        } catch (\Throwable $e) {
            // Trả về lỗi cụ thể để mình biết MongoDB đang gặp vấn đề gì
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
    // ========================================================
    // 9. API LƯU THÔNG TIN NGÂN HÀNG VÀO DATABASE MONGODB
    // ========================================================
    public function updateBankInfo(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản!']);
            }

            // MongoDB sẽ tự động tạo thêm 3 cột này vào bảng users
            $user->bank_name = $request->bank_name;
            $user->account_name = $request->account_name;
            $user->account_number = $request->account_number;
            $user->save();

            return response()->json(['success' => true, 'message' => 'Đã lưu thông tin vào CSDL', 'user' => $user]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ========================================================
    // 10. API LẤY THÔNG TIN NGÂN HÀNG TỪ DATABASE
    // ========================================================
    public function getBankInfo(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            if ($user && $user->bank_name) {
                return response()->json([
                    'success' => true, 
                    'bankInfo' => [
                        'bankName' => $user->bank_name,
                        'accountName' => $user->account_name,
                        'accountNumber' => $user->account_number
                    ]
                ]);
            }
            return response()->json(['success' => false]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    // ========================================================
    // 11. API HỦY YÊU CẦU RÚT TIỀN (TỪ REACT)
    // ========================================================
    public function cancelPayout(Request $request)
    {
        try {
            // Tìm yêu cầu đang 'pending' của user này và xóa nó
            $payout = \App\Models\PayoutRequest::where('user_id', $request->user_id)
                                              ->where('status', 'pending')
                                              ->first();
            if ($payout) {
                $payout->delete();
                return response()->json(['success' => true, 'message' => 'Đã hủy yêu cầu rút tiền']);
            }
            return response()->json(['success' => false, 'message' => 'Không tìm thấy yêu cầu đang chờ']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}