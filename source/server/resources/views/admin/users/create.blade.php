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
                @csrf 
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Họ và Tên</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Nhập tên" value="{{ old('name') }}">
                        @error('name')
                            <small class="text-danger font-weight-bold">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Địa chỉ Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Nhập email" value="{{ old('email') }}">
                        @error('email')
                            <small class="text-danger font-weight-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Nhập mật khẩu">
                        @error('password')
                            <small class="text-danger font-weight-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Quyền hạn</label>
                        <select class="form-control" name="role">
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Học viên</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Giảng viên</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
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