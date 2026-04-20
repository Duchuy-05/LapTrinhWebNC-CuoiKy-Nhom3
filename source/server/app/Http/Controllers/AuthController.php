<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationMail;
use App\Models\EmailVerification;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ==========================================
    // BƯỚC 1: Đăng ký - Tạo OTP và gửi email
    // ==========================================
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'password' => [
                'required', 
                'string', 
                'min:6',
                'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>\-_]).+$/',
                'confirmed'
            ]
        ], [
            'name.required'     => 'Vui lòng nhập họ và tên.',
            'email.required'    => 'Vui lòng nhập email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed'=> 'Mật khẩu xác nhận không khớp.',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex'    => 'Mật khẩu phải chứa ít nhất 1 chữ in hoa và 1 ký tự đặc biệt.',
        ]);

        // Kiểm tra email đã tồn tại chưa
        if (User::where('email', $fields['email'])->exists()) {
            return response()->json([
                'errors' => ['email' => ['Email này đã được đăng ký, vui lòng sử dụng email khác!']]
            ], 422);
        }

        // Tạo mã OTP 6 chữ số
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Xóa bản ghi cũ (nếu có) và lưu OTP mới
        EmailVerification::where('email', $fields['email'])->delete();
        EmailVerification::create([
            'email'      => $fields['email'],
            'code'       => bcrypt($otp),
            'name'       => $fields['name'],
            'password'   => $fields['password'], // Lưu plain text, User model tự hash qua cast 'hashed'
            'expires_at' => now()->addMinutes(10),
            'verified'   => false,
        ]);

        // Gửi email
        try {
            Mail::to($fields['email'])->send(new EmailVerificationMail($otp, $fields['name']));
        } catch (\Exception $e) {
            Log::error('[EmailVerification] Gửi email thất bại khi đăng ký', [
                'email' => $fields['email'],
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Không thể gửi email xác nhận. Vui lòng thử lại.',
            ], 500);
        }

        return response()->json([
            'status'  => 'needs_verification',
            'email'   => $fields['email'],
            'message' => 'Mã xác nhận đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.'
        ], 200);
    }

    // ==========================================
    // BƯỚC 2: Xác thực OTP - Tạo tài khoản
    // ==========================================
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ], [
            'code.required' => 'Vui lòng nhập mã xác nhận.',
            'code.size'     => 'Mã xác nhận phải có đúng 6 chữ số.',
        ]);

        $verification = EmailVerification::where('email', $request->email)
            ->where('verified', false)
            ->first();

        if (!$verification) {
            return response()->json([
                'message' => 'Không tìm thấy yêu cầu xác thực. Vui lòng đăng ký lại.'
            ], 404);
        }

        if (now()->isAfter($verification->expires_at)) {
            $verification->delete();
            return response()->json([
                'message' => 'Mã xác nhận đã hết hạn. Vui lòng đăng ký lại.'
            ], 422);
        }

        if (!Hash::check($request->code, $verification->code)) {
            return response()->json([
                'message' => 'Mã xác nhận không đúng. Vui lòng kiểm tra lại!'
            ], 422);
        }

        // Tạo user
        $user = User::create([
            'name'     => $verification->name,
            'email'    => $verification->email,
            'password' => $verification->password,
            'role'     => 'user',
        ]);

        $verification->delete();
        $token = $user->createToken('studyhub_token')->plainTextToken;

        return response()->json([
            'user'    => $user,
            'token'   => $token,
            'message' => 'Xác thực email thành công! Chào mừng bạn đến với StudyHub.'
        ], 201);
    }

    // ==========================================
    // Gửi lại OTP
    // ==========================================
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $verification = EmailVerification::where('email', $request->email)
            ->where('verified', false)
            ->first();

        if (!$verification) {
            return response()->json([
                'message' => 'Không tìm thấy yêu cầu xác thực. Vui lòng đăng ký lại.'
            ], 404);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $verification->update([
            'code'       => bcrypt($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($verification->email)->send(
                new EmailVerificationMail($otp, $verification->name)
            );
        } catch (\Exception $e) {
            Log::error('[EmailVerification] Gửi email thất bại khi gửi lại OTP', [
                'email' => $verification->email,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Không thể gửi email. Vui lòng thử lại.'], 500);
        }

        return response()->json(['message' => 'Mã xác nhận mới đã được gửi đến email của bạn.'], 200);
    }

    // ==========================================
    // Đăng nhập
    // ==========================================
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Email hoặc mật khẩu không chính xác!'], 401);
        }

        $token = $user->createToken('studyhub_token')->plainTextToken;
        return response(['user' => $user, 'token' => $token], 200);
    }

    // ==========================================
    // Đăng nhập Google
    // ==========================================
    public function googleLogin(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);
            
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'      => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password'  => bcrypt(uniqid()),
                    'role'      => 'user',
                ]
            );

            $token = $user->createToken('studyhub_token')->plainTextToken;

            return response([
                'user'    => $user,
                'token'   => $token,
                'message' => 'Đăng nhập Google thành công'
            ], 200);

        } catch (\Exception $e) {
            return response([
                'message' => 'Xác thực Google thất bại hoặc Token không hợp lệ.',
                'error'   => $e->getMessage()
            ], 401);
        }
    }
}