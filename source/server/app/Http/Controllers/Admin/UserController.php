<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all(); 
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    // Nhận dữ liệu từ Form và lưu vào MongoDB
    public function store(Request $request)
    {
        // 1. Tạo một User mới
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        // Mã hóa mật khẩu trước khi lưu để bảo mật
        $user->password = bcrypt($request->password); 
        
        // 2. Lưu vào MongoDB
        $user->save();

        // 3. Quay trở về trang danh sách
        return redirect()->route('admin.users.index');
    }

    // 1. Hiển thị Form Sửa Người dùng
    public function edit($id)
    {
        // Tìm user theo ID (MongoDB tự động hiểu ObjectId)
        $user = User::findOrFail($id); 
        return view('admin.users.edit', compact('user'));
    }

    // 2. Nhận dữ liệu từ Form Sửa và cập nhật vào MongoDB
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role; // Cập nhật quyền (admin hoặc user)

        // Chỉ cập nhật mật khẩu nếu Admin có gõ mật khẩu mới
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        
        $user->save();

        // Quay về trang danh sách
        return redirect()->route('admin.users.index');
    }

    // 3. Xóa Người dùng
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index');
    }
}