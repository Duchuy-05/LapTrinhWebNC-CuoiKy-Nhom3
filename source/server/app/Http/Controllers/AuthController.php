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
            'email' => 'required|string|unique:users,email', // Kiểm tra 
            'password' => 'required|string|confirmed' 
            // Lưu ý: React phải gửi lên trường password_confirmation
        ]);

        // 2. Tạo user mới vào database
        $user = User::create([
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']) // Mã hóa mật khẩu
        ]);

        // 3. Tạo Token
        $token = $user->createToken('studyhub_token')->plainTextToken;

        // 4. Trả về cho React
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