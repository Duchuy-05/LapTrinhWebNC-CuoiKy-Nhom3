<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order; 
use App\Models\PayoutRequest; // Khai báo thêm PayoutRequest
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
    // 2. ĐĂNG KÝ VÀ GỬI OTP
    public function register(Request $request)
    {
        if(User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email này đã được sử dụng!'])->header('Access-Control-Allow-Origin', '*');
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = 'user';
            
            // 1. Tạo mã OTP ngẫu nhiên 6 số
            $otp = rand(100000, 999999);
            
            // Lưu OTP vào MongoDB (MongoDB sẽ tự động thêm cột này)
            $user->otp = $otp; 
            $user->otp_expires_at = now()->addMinutes(10); // Cài đặt hết hạn sau 10 phút
            $user->save();

            // 2. Gửi email chứa mã OTP cho học viên
            Mail::raw("Chào {$user->name},\n\nMã OTP xác thực tài khoản StudyHub của bạn là: {$otp}\nMã này sẽ hết hạn sau 10 phút.\n\nTrân trọng,\nĐội ngũ StudyHub", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Mã xác thực OTP - StudyHub');
            });

            // 3. Trả kết quả về cho React để mở form nhập mã
            return response()->json([
                'success' => true, 
                'status' => 'needs_verification',
                'email' => $user->email,
                'message' => 'Đã gửi mã OTP. Vui lòng kiểm tra hộp thư!'
            ])->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Không thể gửi email OTP. Vui lòng báo Admin kiểm tra cấu hình SMTP! Chi tiết: ' . $e->getMessage()
            ])->header('Access-Control-Allow-Origin', '*');
        }
    }

    // ========================================================
    // 2.5 API XÁC THỰC MÃ OTP (ĐỒNG BỘ VỚI REACT)
    // ========================================================
    public function verifyOtp(Request $request)
    {
        try {
            // Tìm user dựa vào email mà React gửi lên
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy yêu cầu xác thực. Vui lòng đăng ký lại.'], 400);
            }

            // So sánh OTP (Chú ý: React gửi lên bằng biến tên là 'code')
            if ((string)$user->otp !== (string)$request->code) {
                return response()->json(['success' => false, 'message' => 'Mã xác thực không chính xác!'], 400);
            }

            // Kiểm tra thời gian hết hạn (10 phút)
            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['success' => false, 'message' => 'Mã xác thực đã hết hạn! Vui lòng gửi lại mã.'], 400);
            }

            // NẾU THÀNH CÔNG: Xóa OTP cũ đi để bảo mật và cấp Token đăng nhập
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true, 
                'message' => 'Xác thực thành công!',
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }

    // ========================================================
    // 2.6 API GỬI LẠI MÃ OTP (DÀNH CHO NÚT GỬI LẠI)
    // ========================================================
    public function resendOtp(Request $request)
    {
        try {
            $user = \App\Models\User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản!'], 400);
            }

            // Tạo mã OTP mới và gia hạn 10 phút
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            // Gửi email chứa mã mới
            Mail::raw("Chào {$user->name},\n\nMã OTP MỚI để xác thực tài khoản StudyHub của bạn là: {$otp}\nMã này sẽ hết hạn sau 10 phút.\n\nTrân trọng,\nĐội ngũ StudyHub", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Mã xác thực OTP (Gửi lại) - StudyHub');
            });

            return response()->json(['success' => true, 'message' => 'Đã gửi lại mã OTP']);
            
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
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

    // ========================================================
    // 12. API LẤY DANH SÁCH HỌC VIÊN (KHỚP TẤT CẢ ĐỊNH DẠNG ID VÀ UUID)
    // ========================================================
    public function getMyStudents(Request $request)
    {
        try {
            $lecturerId = (string) $request->input('user_id');

            // 1. TÌM KHÓA HỌC CỦA ĐÚNG GIẢNG VIÊN NÀY
            $allCourses = \App\Models\Course::all();
            $myCourses = [];
            $myCourseIds = []; // Mảng chứa toàn bộ các loại ID của khóa học này

            foreach ($allCourses as $c) {
                $author = (string)($c->authorId ?? $c->author_id ?? $c->user_id ?? '');
                
                if ($author === $lecturerId) {
                    $myCourses[] = $c;
                    
                    // Lấy tất cả các định dạng ID có thể được lưu trong bảng Order
                    if (!empty($c->_id)) $myCourseIds[] = (string)$c->_id;
                    if (!empty($c->id)) $myCourseIds[] = (string)$c->id;
                    if (!empty($c->courseGroupId)) $myCourseIds[] = (string)$c->courseGroupId; // Chìa khóa từ ảnh MongoDB của bạn
                }
            }

            // 2. LẤY ĐƠN HÀNG THUỘC VỀ CÁC KHÓA HỌC TRÊN
            $allOrders = \App\Models\Order::all();
            $validOrders = [];
            
            foreach ($allOrders as $o) {
                $status = mb_strtolower($o->status ?? '', 'UTF-8');
                if (in_array($status, ['completed', 'success', 'thành công', 'thanh cong'])) {
                    
                    $orderCourseId = (string)($o->course_id ?? $o->courseId ?? '');
                    
                    // Kiểm tra xem ID trong hóa đơn có khớp với bất kỳ ID/UUID nào của Giảng viên không
                    if (in_array($orderCourseId, $myCourseIds)) {
                        $validOrders[] = $o;
                    }
                }
            }

            // 3. TÌM THÔNG TIN HỌC VIÊN
            $allUsers = \App\Models\User::all();
            $studentsList = [];

            foreach ($validOrders as $o) {
                $orderCourseId = (string)($o->course_id ?? $o->courseId ?? '');
                $uId = (string)($o->user_id ?? $o->userId ?? '');

                // Lấy tên khóa học tương ứng
                $courseName = 'Khóa học';
                foreach ($myCourses as $c) {
                    if (
                        (string)($c->_id ?? '') === $orderCourseId || 
                        (string)($c->id ?? '') === $orderCourseId || 
                        (string)($c->courseGroupId ?? '') === $orderCourseId
                    ) {
                        $courseName = $c->title ?? $c->name ?? 'Khóa học';
                        break;
                    }
                }

                // Lấy tên và email học viên
                $studentName = 'Học viên';
                $studentEmail = '';
                foreach ($allUsers as $u) {
                    if ((string)($u->_id ?? $u->id) === $uId) {
                        $studentName = $u->name ?? 'Học viên';
                        $studentEmail = $u->email ?? '';
                        break;
                    }
                }

                $studentsList[] = [
                    'order_id' => (string)($o->_id ?? $o->id),
                    'student_name' => $studentName,
                    'student_email' => $studentEmail,
                    'course_name' => $courseName,
                    'amount' => $o->amount ?? 0,
                    'enrolled_at' => $o->created_at ? \Carbon\Carbon::parse($o->created_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s')
                ];
            }

            // Sắp xếp ngày đăng ký mới nhất lên đầu
            usort($studentsList, function($a, $b) {
                return strtotime($b['enrolled_at']) - strtotime($a['enrolled_at']);
            });

            return response()->json(['success' => true, 'data' => $studentsList]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}