@extends('layouts.app')

@section('title', 'Đăng ký học viên | StudyHub')

@section('content')
    <section class="section-block compact-top">
        <div class="container auth-shell">
            <div class="auth-copy">
                <span class="eyebrow">Đăng ký học viên</span>
                <h1>Tạo tài khoản học tập trong vài phút</h1>
                <p>Trang đăng ký công khai chỉ dành cho học viên. Tài khoản giảng viên sẽ do quản trị viên tạo và cấp quyền từ khu quản trị của hệ thống.</p>
            </div>
            <div class="surface-card auth-card">
                <form method="POST" action="{{ route('register') }}" class="form-grid">
                    @csrf
                    <div class="form-field">
                        <label for="name">Họ và tên</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-field">
                        <label for="password">Mật khẩu</label>
                        <input id="password" type="password" name="password" required>
                    </div>
                    <div class="form-field">
                        <label for="password_confirmation">Xác nhận mật khẩu</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                    <button class="button button-block" type="submit">Tạo tài khoản học viên</button>
                </form>
                <p class="form-footnote">Đã có tài khoản? <a class="text-link" href="{{ route('login', ['role' => 'student']) }}">Đăng nhập ngay</a></p>
            </div>
        </div>
    </section>
@endsection