<?php

namespace App\Http\Controllers;

use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use MongoDB\Laravel\Eloquent\Model;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Hàm Đăng ký
    public function register(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'password' => [
                'required', 
                'string', 
                'min:6', // Tối thiểu 6 ký tự
                'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>\-_]).+$/', // Bắt buộc có chữ in hoa và ký tự đặc biệt
                'confirmed'
            ]
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.unique' => 'Email này đã được đăng ký, vui lòng sử dụng email khác!',
            'email.required' => 'Vui lòng nhập email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ in hoa và 1 ký tự đặc biệt.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);
        // 2. Kiểm tra email đã tồn tại chưa
        $existingUser = User::where('email', $fields['email'])->first();
        if ($existingUser) {
            return response()->json([
                'errors' => [
                    'email' => ['Email này đã được đăng ký, vui lòng sử dụng email khác!']
                ]
            ], 422); // 422 là mã lỗi Unprocessable Entity chuẩn của Laravel
        }
        // 3. Tạo user mới vào database
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']), // Mã hóa mật khẩu
            'role'=> 'user' 
        ]);

        // 4. Tạo Token
        $token = $user->createToken('studyhub_token')->plainTextToken;

        // 5. Trả về cho React
        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // Hàm Đăng nhập
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // 1. Tìm user theo email
        $user = User::where('email', $fields['email'])->first();

        // 2. Kiểm tra mật khẩu
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Email hoặc mật khẩu không chính xác!'
            ], 401);
        }

        // 3. Tạo Token mới
        $token = $user->createToken('studyhub_token')->plainTextToken;

        // 4. Trả về kết quả
        return response([
            'user' => $user,
            'token' => $token
        ], 200);
    }
    public function googleLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        try {
            // Xác thực token từ React gửi lên thông qua Google API
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);
            
            // Tìm user theo email, hoặc tạo mới nếu chưa có
            // Lưu ý: Đảm bảo Model User của bạn đã cho phép fillable các trường 'name', 'email', 'google_id'
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    // Tạo một password ngẫu nhiên vì user đăng nhập bằng Google
                    'password' => bcrypt(uniqid()) 
                ]
            );

            // Tạo Token của hệ thống (giống như hàm login bình thường của bạn)
            $token = $user->createToken('studyhub_token')->plainTextToken;

            return response([
                'user' => $user,
                'token' => $token,
                'message' => 'Đăng nhập Google thành công'
            ], 200);

        } catch (\Exception $e) {
            return response([
                'message' => 'Xác thực Google thất bại hoặc Token không hợp lệ.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}