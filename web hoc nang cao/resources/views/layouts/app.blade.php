<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'StudyHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="site-shell">
        <header class="site-header">
            <div class="container nav-wrap">
                <a class="brand" href="{{ route('home') }}">
                    <span class="brand-mark">S</span>
                    <span>
                        <strong>StudyHub</strong>
                        <small>Nền tảng học tập trực tuyến hiện đại</small>
                    </span>
                </a>

                <nav class="main-nav">
                    <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Trang chủ</a>
                    <a href="{{ route('courses.index') }}" @class(['active' => request()->routeIs('courses.*', 'lessons.*')])>Khóa học</a>
                    <a href="{{ route('pages.show', 'guidelines') }}" @class(['active' => request()->is('pages/guidelines')])>Hướng dẫn</a>
                    <a href="{{ route('pages.show', 'regulations') }}" @class(['active' => request()->is('pages/regulations')])>Quy định</a>
                </nav>

                <div class="nav-actions">
                    @auth
                        @if (auth()->user()->isAdmin())
                            <a class="button button-muted" href="{{ route('admin.dashboard') }}">Quản trị</a>
                        @elseif (auth()->user()->isInstructor())
                            <a class="button button-muted" href="{{ route('instructor.dashboard') }}">Giảng viên</a>
                        @else
                            <a class="button button-muted" href="{{ route('dashboard') }}">Học tập</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="button button-ghost" type="submit">Đăng xuất</button>
                        </form>
                    @else
                        <a class="button button-ghost" href="{{ route('login') }}">Đăng nhập</a>
                        <a class="button" href="{{ route('register') }}">Đăng ký học viên</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="page-main">
            <div class="container">
                @include('partials.flash')
            </div>

            @yield('content')
        </main>

        <footer class="site-footer">
            <div class="container footer-grid">
                <div>
                    <h3>StudyHub</h3>
                    <p>Website học tập được xây dựng bằng PHP Laravel, sẵn sàng chạy với XAMPP và mở rộng thành hệ thống đào tạo trực tuyến hoàn chỉnh.</p>
                </div>
                <div>
                    <h4>Cổng truy cập</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('login', ['role' => 'student']) }}">Đăng nhập học viên</a></li>
                        <li><a href="{{ route('login', ['role' => 'instructor']) }}">Đăng nhập giảng viên</a></li>
                        <li><a href="{{ route('login', ['role' => 'admin']) }}">Đăng nhập quản trị</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Định hướng tiếp theo</h4>
                    <p>Bổ sung bài kiểm tra tương tác, chứng chỉ, thanh toán, lớp học trực tuyến và tối ưu hiệu năng cho nhiều người dùng đồng thời.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>