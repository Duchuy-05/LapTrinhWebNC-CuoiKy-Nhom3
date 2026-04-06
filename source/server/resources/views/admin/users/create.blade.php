@extends('layouts.admin')

@section('title', 'Thêm Người Dùng Mới')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Điền thông tin</h3>
            </div>
            
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf <div class="card-body">
                    <div class="form-group">
                        <label for="name">Họ và Tên</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Địa chỉ Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                    </div>

                    <div class="form-group">
    <label>Quyền hạn</label>
    <select class="form-control" name="role">
        <option value="user">Học viên</option>
        <option value="teacher">Giảng viên</option>
        <option value="admin">Quản trị viên</option>
    </select>
</div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Lưu người dùng</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default float-right">Hủy</a>
                </div>
                
            </form>
        </div>
    </div>
</div>
@endsection