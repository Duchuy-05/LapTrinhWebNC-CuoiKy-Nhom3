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
        // Kiểm tra xem User đã đăng nhập chưa VÀ role có phải là 'admin' không
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request); // Đúng là Admin thì mời đi tiếp
        }

        // Nếu là học sinh (hoặc role khác), đá văng ra trang chủ hoặc báo lỗi
        // Tạm thời mình cho đá văng ra trang chủ (bạn có thể đổi link khác)
        return redirect('/')->with('error', 'Bạn không có quyền truy cập khu vực này!');
    }
}