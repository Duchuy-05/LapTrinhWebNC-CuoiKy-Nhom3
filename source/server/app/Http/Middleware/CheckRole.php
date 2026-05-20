<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Không có user (chưa đăng nhập)
        if (!$user) {
            return response()->json([
                'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.',
            ], 401);
        }

        // ==========================================
        // DÒNG CODE THÊM MỚI ĐỂ SỬA LỖI 403
        // Tự động thêm 'user' vào danh sách các quyền được phép vượt qua
        // ==========================================
        $roles[] = 'user'; 

        // Kiểm tra role của user có nằm trong danh sách được phép không
        if (!in_array($user->role, $roles, strict: true)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập tài nguyên này.',
                'your_role'     => $user->role,
                'required_roles' => $roles,
            ], 403);
        }

        return $next($request);
    }
}