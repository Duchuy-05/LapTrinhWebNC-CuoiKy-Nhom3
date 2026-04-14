<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Bước 1: Nếu chưa đăng nhập gì cả -> Đuổi về trang Đăng nhập Admin
        if (!Auth::check()) {
            return redirect('admin/login');
        }

        // Bước 2: Nếu ĐÃ đăng nhập và đúng là 'admin' -> Mở cửa cho vào Dashboard
        if (Auth::user()->role === 'admin') {
            return $next($request);
        }

        // Bước 3: Đã đăng nhập nhưng KHÔNG PHẢI admin (Học viên, Giảng viên...)
        // Bắt buộc phải Hủy phiên đăng nhập của người này
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Đuổi về trang Đăng nhập và ném ra câu thông báo lỗi
        return redirect('admin/login')->withErrors([
            'email' => 'Quyền hạn của bạn không đủ để truy cập trang quản trị.'
        ]);
    }
}