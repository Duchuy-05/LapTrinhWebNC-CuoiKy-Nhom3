<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $selectedRole = $request->query('role');

        if (! in_array($selectedRole, ['student', 'instructor', 'admin'], true)) {
            $selectedRole = null;
        }

        return view('auth.login', compact('selectedRole'));
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'role' => ['required', Rule::in(['student', 'instructor', 'admin'])],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'role.required' => 'Vui lòng chọn thân phận đăng nhập trước khi tiếp tục.',
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Tài khoản hiện đang tạm khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        if ($user->role !== $credentials['role']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'role' => 'Vai trò đã chọn không khớp với tài khoản này. Vui lòng chọn lại đúng thân phận đăng nhập.',
            ]);
        }

        return redirect()->intended(match ($user->role) {
            'admin' => route('admin.dashboard'),
            'instructor' => route('instructor.dashboard'),
            default => route('dashboard'),
        });
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}