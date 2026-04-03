@extends('layouts.app')

@section('title', 'Đăng nhập | StudyHub')

@section('content')
    @php
        $activeRole = old('role', $selectedRole);
    @endphp

    <section class="section-block compact-top">
        <div class="container auth-shell auth-shell-wide">
            <div class="auth-copy">
                <span class="eyebrow">Đăng nhập theo vai trò</span>
                <h1>Bắt đầu bằng việc chọn đúng thân phận đăng nhập</h1>
                <p>Để vào đúng khu vực làm việc ngay từ đầu, bạn hãy chọn một trong ba vai trò bên dưới. Sau đó hệ thống mới mở biểu mẫu email và mật khẩu tương ứng.</p>
            </div>
            <div class="surface-card auth-card auth-card-large">
                <form method="POST" action="{{ route('login') }}" class="form-grid" id="role-login-form">
                    @csrf
                    <div class="role-picker">
                        <label class="role-card {{ $activeRole === 'student' ? 'is-selected' : '' }}">
                            <input type="radio" name="role" value="student" @checked($activeRole === 'student')>
                            <span class="role-card__badge">Học viên</span>
                            <strong>Học tập và theo dõi tiến độ</strong>
                            <small>Dành cho người tham gia khóa học</small>
                        </label>
                        <label class="role-card {{ $activeRole === 'instructor' ? 'is-selected' : '' }}">
                            <input type="radio" name="role" value="instructor" @checked($activeRole === 'instructor')>
                            <span class="role-card__badge">Giảng viên</span>
                            <strong>Quản lý lớp học và nội dung</strong>
                            <small>Tài khoản do quản trị viên cấp</small>
                        </label>
                        <label class="role-card {{ $activeRole === 'admin' ? 'is-selected' : '' }}">
                            <input type="radio" name="role" value="admin" @checked($activeRole === 'admin')>
                            <span class="role-card__badge">Quản trị</span>
                            <strong>Điều phối hệ thống</strong>
                            <small>Phân quyền, tạo giảng viên, quản trị nội dung</small>
                        </label>
                    </div>

                    <div class="login-fields {{ $activeRole ? '' : 'is-hidden' }}" id="login-fields">
                        <div class="form-field">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="form-field">
                            <label for="password">Mật khẩu</label>
                            <input id="password" type="password" name="password" required>
                        </div>
                        <label class="checkbox-field">
                            <input type="checkbox" name="remember" value="1">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <button class="button button-block" type="submit">Tiếp tục đăng nhập</button>
                    </div>
                </form>

                <p class="form-footnote">Chỉ học viên được tự đăng ký tài khoản mới. <a class="text-link" href="{{ route('register') }}">Đăng ký học viên</a></p>
            </div>
        </div>
    </section>

    <script>
        document.querySelectorAll('input[name="role"]').forEach((input) => {
            input.addEventListener('change', () => {
                document.querySelectorAll('.role-card').forEach((card) => card.classList.remove('is-selected'));
                input.closest('.role-card')?.classList.add('is-selected');
                document.getElementById('login-fields')?.classList.remove('is-hidden');
            });
        });
    </script>
@endsection