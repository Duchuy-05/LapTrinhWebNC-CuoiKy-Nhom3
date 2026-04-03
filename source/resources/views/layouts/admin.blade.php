<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản trị | StudyHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="portal-body">
    <div class="portal-shell">
        <aside class="portal-sidebar">
            <a class="brand brand-admin" href="{{ route('admin.dashboard') }}">
                <span class="brand-mark">A</span>
                <span>
                    <strong>Admin Panel</strong>
                    <small>Điều phối toàn bộ hệ thống</small>
                </span>
            </a>

            <nav class="portal-nav">
                <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>Tổng quan</a>
                <a href="{{ route('admin.categories.index') }}" @class(['active' => request()->routeIs('admin.categories.*')])>Danh mục</a>
                <a href="{{ route('admin.courses.index') }}" @class(['active' => request()->routeIs('admin.courses.*')])>Khóa học</a>
                <a href="{{ route('admin.lessons.index') }}" @class(['active' => request()->routeIs('admin.lessons.*')])>Bài học</a>
                <a href="{{ route('admin.users.index') }}" @class(['active' => request()->routeIs('admin.users.*')])>Người dùng</a>
                <a href="{{ route('admin.announcements.index') }}" @class(['active' => request()->routeIs('admin.announcements.*')])>Thông báo</a>
                <a href="{{ route('admin.site-contents.index') }}" @class(['active' => request()->routeIs('admin.site-contents.*')])>Nội dung tĩnh</a>
                <a href="{{ route('home') }}">Xem website</a>
            </nav>

            <div class="portal-user-box">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button button-ghost button-block" type="submit">Đăng xuất</button>
                </form>
            </div>
        </aside>

        <main class="portal-content">
            <div class="container container-tight">
                @include('partials.flash')
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>