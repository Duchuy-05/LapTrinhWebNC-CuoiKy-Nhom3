<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Giảng viên | StudyHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="portal-body portal-body-instructor">
    <div class="portal-shell">
        <aside class="portal-sidebar portal-sidebar-instructor">
            <a class="brand brand-admin" href="{{ route('instructor.dashboard') }}">
                <span class="brand-mark">G</span>
                <span>
                    <strong>Cổng giảng viên</strong>
                    <small>Quản lý lớp học và nội dung giảng dạy</small>
                </span>
            </a>

            <nav class="portal-nav">
                <a href="{{ route('instructor.dashboard') }}" @class(['active' => request()->routeIs('instructor.dashboard')])>Bảng điều khiển</a>
                <a href="{{ route('instructor.courses.index') }}" @class(['active' => request()->routeIs('instructor.courses.*')])>Khóa học của tôi</a>
                <a href="{{ route('instructor.lessons.index') }}" @class(['active' => request()->routeIs('instructor.lessons.*')])>Bài học</a>
                <a href="{{ route('instructor.posts.index') }}" @class(['active' => request()->routeIs('instructor.posts.*')])>Bài đăng</a>
                <a href="{{ route('instructor.quizzes.index') }}" @class(['active' => request()->routeIs('instructor.quizzes.*', 'instructor.questions.*')])>Bài kiểm tra</a>
                <a href="{{ route('instructor.students.index') }}" @class(['active' => request()->routeIs('instructor.students.*')])>Học viên</a>
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