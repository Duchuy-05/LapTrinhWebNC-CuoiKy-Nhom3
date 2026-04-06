<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FrontendApiController extends Controller
{
    // API Xử lý Đăng nhập
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Đăng nhập đúng -> Trả về thông tin user
            return response()->json(['success' => true, 'user' => $user])
                             ->header('Access-Control-Allow-Origin', '*');
        }

        // Sai mật khẩu hoặc email
        return response()->json(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!'])
                         ->header('Access-Control-Allow-Origin', '*');
    }

    // API Xử lý Đăng ký
    public function register(Request $request)
    {
        // Kiểm tra xem email đã tồn tại chưa (tùy chọn thêm)
        if(User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email này đã được sử dụng!'])
                             ->header('Access-Control-Allow-Origin', '*');
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'user';
        $user->save();

        return response()->json(['success' => true, 'user' => $user])
                         ->header('Access-Control-Allow-Origin', '*');
    }

    // API TẠO LINK THANH TOÁN
    public function createPayment(Request $request)
    {
        // 1. Chắc chắn múi giờ chuẩn VN
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        // BƯỚC QUAN TRỌNG: Gắn cứng Key trực tiếp để bỏ qua lỗi đọc file .env
        // (Khi nào test thành công, chúng ta mới chuyển lại vào .env sau)
        $vnp_TmnCode = "PJUY8IX3";
        $vnp_HashSecret = "P6WY9PYAPMCCCB50A8XKT605SXIADQOT";
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://127.0.0.1:8000/api/vnpay-return";

        // 2. Lưu đơn hàng
        $order = new \App\Models\Order();
        $order->user_id = $request->user_id;
        $order->course_id = $request->course_id;
        $order->amount = $request->amount;
        $order->payment_method = 'VNPay';
        $order->status = 'pending';
        $order->save();

        // 3. Chuẩn bị biến gửi VNPay
        $vnp_TxnRef = (string) $order->id;
        
        // BƯỚC QUAN TRỌNG: Viết liền không dấu cách để tránh lỗi urlencode
        $vnp_OrderInfo = "ThanhToanKhoaHoc_" . $vnp_TxnRef; 
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = intval($request->amount) * 100;
        $vnp_Locale = 'vn';
        
        $vnp_IpAddr = $request->ip();
        if ($vnp_IpAddr == '::1' || empty($vnp_IpAddr)) {
            $vnp_IpAddr = '127.0.0.1';
        }

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        // 4. Tạo mã băm chữ ký
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return response()->json(['success' => true, 'url' => $vnp_Url])->header('Access-Control-Allow-Origin', '*');
    }

    // API NHẬN KẾT QUẢ TỪ VNPAY
    public function vnpayReturn(Request $request)
    {
        // Gắn cứng HashSecret để lúc kiểm tra chữ ký trả về cũng không bị lỗi
        $vnp_HashSecret = "P6WY9PYAPMCCCB50A8XKT605SXIADQOT"; 

        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        if ($secureHash == $vnp_SecureHash) {
            $order = \App\Models\Order::find($request->vnp_TxnRef);
            
            if ($request->vnp_ResponseCode == '00') {
                $order->status = 'completed';
                $order->save();
                
                // CHÚ Ý: Đường dẫn nhảy về file HTML của bạn. 
                // Bạn hãy chắc chắn sửa đường dẫn này thành ĐÚNG VỊ TRÍ file index.html trên máy bạn nhé!
                return redirect()->away('file:///C:/Duong/Dan/Toi/File/Cua/Ban/index.html?payment=success'); 
            } else {
                $order->status = 'cancelled';
                $order->save();
                return redirect()->away('file:///C:/Duong/Dan/Toi/File/Cua/Ban/index.html?payment=failed');
            }
        } else {
            return response()->json(['message' => 'Chữ ký VNPay trả về không hợp lệ!']);
        }
    }
}