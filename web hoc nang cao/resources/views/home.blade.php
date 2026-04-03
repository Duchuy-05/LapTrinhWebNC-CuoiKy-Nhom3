@extends('layouts.app')

@section('title', 'StudyHub | Nền tảng học tập trực tuyến')

@section('content')
    <section class="hero-section hero-modern">
        <div class="container hero-grid hero-grid-modern">
            <div>
                <h1>Học tập ở bất kỳ đâu với nền tảng được tổ chức bài bản cho học viên, giảng viên.</h1>
                <p class="hero-copy">StudyHub giúp bạn xây dựng hệ thống đào tạo trực tuyến hiện đại: quản lý khóa học, bài học, bài đăng, bài kiểm tra, tiến độ học tập và quyền truy cập theo từng vai trò.</p>
                <div class="hero-actions">
                    <a class="button" href="{{ route('courses.index') }}">Khám phá khóa học</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section-block compact-top">
        <div class="container access-grid">
            <a class="access-card" href="{{ route('login', ['role' => 'student']) }}">
                <span class="access-card__icon">HV</span>
                <h3>Học viên</h3>
                <p>Đăng nhập để tham gia khóa học, xem bài giảng và theo dõi tiến độ cá nhân.</p>
            </a>
            <a class="access-card" href="{{ route('login', ['role' => 'instructor']) }}">
                <span class="access-card__icon">GV</span>
                <h3>Giảng viên</h3>
                <p>Quản lý khóa học được giao, đăng bài, cập nhật bài học và bài kiểm tra.</p>
            </a>
        </div>
    </section>

    <section class="section-block">
        <div class="container section-heading">
            <div>
                <span class="eyebrow">Thông báo hệ thống</span>
                <h2>Cập nhật mới nhất</h2>
            </div>
        </div>
        <div class="container card-grid card-grid-3">
            @forelse ($announcements as $announcement)
                <article class="info-card">
                    <h3>{{ $announcement->title }}</h3>
                    <p>{{ $announcement->body }}</p>
                    @if ($announcement->cta_label && $announcement->cta_url)
                        <a class="text-link" href="{{ $announcement->cta_url }}">{{ $announcement->cta_label }}</a>
                    @endif
                </article>
            @empty
                <article class="info-card">
                    <h3>Chưa có thông báo</h3>
                    <p>Quản trị viên có thể thêm thông báo, hướng dẫn học tập hoặc các cập nhật quan trọng từ khu quản trị.</p>
                </article>
            @endforelse
        </div>
    </section>

    <section class="section-block section-soft">
        <div class="container section-heading">
            <div>
                <span class="eyebrow">Khóa học nổi bật</span>
                <h2>Nội dung đang được quan tâm</h2>
            </div>
            <a class="text-link" href="{{ route('courses.index') }}">Xem tất cả khóa học</a>
        </div>
        <div class="container card-grid card-grid-3">
            @foreach ($featuredCourses as $course)
                @include('partials.course-card', ['course' => $course])
            @endforeach
        </div>
    </section>

    <section class="section-block compact-top">
        <div class="container section-heading">
            <div>
                <span class="eyebrow">Bài đăng từ giảng viên</span>
                <h2>Nội dung mới được chia sẻ</h2>
            </div>
        </div>
        <div class="container card-grid card-grid-3">
            @forelse ($latestPosts as $post)
                <article class="info-card">
                    <span class="pill">{{ $post->course->title }}</span>
                    <h3>{{ $post->title }}</h3>
                    <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 120) }}</p>
                    <small>Tác giả: {{ $post->author->name }}</small>
                </article>
            @empty
                <article class="info-card">
                    <h3>Chưa có bài đăng mới</h3>
                    <p>Ngay khi giảng viên bắt đầu cập nhật khóa học, các bài viết mới sẽ xuất hiện tại đây.</p>
                </article>
            @endforelse
        </div>
    </section>
@endsection