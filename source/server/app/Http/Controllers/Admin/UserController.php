<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // 1. Hiển thị danh sách
    public function index()
    {
        $users = User::all(); 
        return view('admin.users.index', compact('users'));
    }

    // 2. Hiển thị form Thêm mới
    public function create()
    {
        return view('admin.users.create');
    }

    // 3. Nhận dữ liệu từ Form và lưu vào MongoDB
    public function store(Request $request)
    {
        // --- BƯỚC 1: CHỐT CHẶN KIỂM TRA DỮ LIỆU ---
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email', // Cấm trùng email trong bảng users
            'password' => 'required|min:6',
        ], [
            'name.required'     => 'Vui lòng nhập tên người dùng.',
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Định dạng email không hợp lệ.',
            'email.unique'      => 'Email này đã được sử dụng, vui lòng chọn email khác!',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.'
        ]);

        // --- BƯỚC 2: NẾU QUA ĐƯỢC CHỐT CHẶN, TIẾN HÀNH LƯU ---
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password); 
        
        // Nếu form thêm mới của bạn có chọn role thì mở comment dòng dưới:
        // $user->role = $request->role; 
        
        $user->save();

        // --- BƯỚC 3: QUAY VỀ KÈM THÔNG BÁO THÀNH CÔNG ---
        return redirect()->route('admin.users.index')->with('success', 'Thêm người dùng thành công!');
    }

    // 4. Hiển thị Form Sửa Người dùng
    public function edit($id)
    {
        $user = User::findOrFail($id); 
        return view('admin.users.edit', compact('user'));
    }

    // 5. Nhận dữ liệu từ Form Sửa và cập nhật vào MongoDB
    public function update(Request $request, $id)
    {
        // --- BƯỚC 1: KIỂM TRA DỮ LIỆU SỬA ---
        // Lưu ý: MongoDB dùng '_id' làm khóa chính, nên ta phải chỉ định rõ để nó loại trừ email của chính tài khoản này ra
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id . ',_id', 
        ], [
            'name.required'  => 'Vui lòng nhập tên người dùng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique'   => 'Email này đã bị người khác sử dụng!'
        ]);

        $user = User::findOrFail($id);
        
        $user->name = $request->name;
        $user->email = $request->email;
        
        // Cập nhật quyền (nếu form có gửi lên)
        if ($request->has('role')) {
            $user->role = $request->role; 
        }

        // --- BƯỚC 2: CHỈ CẬP NHẬT MẬT KHẨU NẾU CÓ NHẬP ---
        if ($request->filled('password')) {
            // Kiểm tra thêm độ dài nếu có nhập mật khẩu mới
            $request->validate([
                'password' => 'min:6'
            ], [
                'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'
            ]);
            
            $user->password = bcrypt($request->password);
        }
        
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật thông tin thành công!');
    }

    // 6. Xóa Người dùng
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa người dùng thành công!');
    }
}