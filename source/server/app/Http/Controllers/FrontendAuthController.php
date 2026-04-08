<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FrontendAuthController extends Controller
{
    // 1. Hiển thị form Đăng nhập
    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    // 2. Xử lý Đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Đăng nhập thành công, quay về trang chủ
            return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
        }

        // Đăng nhập thất bại
        return back()->with('error', 'Email hoặc mật khẩu không chính xác!');
    }

    // 3. Hiển thị form Đăng ký
    public function showRegister()
    {
        return view('frontend.auth.register');
    }

    // 4. Xử lý Đăng ký Học viên mới
    public function register(Request $request)
    {
        // Tạo tài khoản mới
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password); // Băm mật khẩu bảo mật
        $user->role = 'user'; // Mặc định role là học viên (user)
        $user->save();

        // Tự động đăng nhập luôn sau khi đăng ký xong
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công! Chào mừng bạn.');
    }

    // 5. Đăng xuất
    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}