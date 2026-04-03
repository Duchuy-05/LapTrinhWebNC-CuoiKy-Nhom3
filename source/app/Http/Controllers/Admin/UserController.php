<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->withCount('enrollments')
            ->orderBy('name')
            ->paginate(12);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'instructor', 'student'])],
            'bio' => ['nullable', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'bio' => $validated['bio'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Đã tạo tài khoản mới.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'instructor', 'student'])],
            'bio' => ['nullable', 'string'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'bio' => $validated['bio'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('admin.users.index')->with('status', 'Đã cập nhật tài khoản.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Bạn không thể tự xóa tài khoản hiện tại.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Đã xóa tài khoản.');
    }
}