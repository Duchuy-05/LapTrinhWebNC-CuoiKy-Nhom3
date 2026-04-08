@extends('layouts.admin')

@section('title', 'Sửa Thông Tin Người Dùng')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Cập nhật thông tin</h3>
            </div>
            
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf 
                @method('PUT') <div class="card-body">
                    <div class="form-group">
                        <label for="name">Họ và Tên</label>
                        <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Địa chỉ Email</label>
                        <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Quyền hạn</label>
                        <select class="form-control" name="role">
                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>Học viên</option>
                            <option value="teacher" {{ $user->role == 'teacher' ? 'selected' : '' }}>Giảng viên</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu mới (Để trống nếu không muốn đổi)</label>
                        <input type="password" class="form-control" name="password" placeholder="Nhập mật khẩu mới...">
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default float-right">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection