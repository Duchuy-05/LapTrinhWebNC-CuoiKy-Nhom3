@extends('layouts.admin')

@section('title', 'Tổng quan quản trị | StudyHub')

@section('content')
    <section class="portal-header">
        <div>
            <span class="eyebrow">Quản trị hệ thống</span>
            <h1>Bảng điều khiển tổng quan</h1>
        </div>
    </section>

    <section class="card-grid card-grid-4 compact-cards">
        <article class="info-card stat-card-inline"><strong>{{ $stats['users'] }}</strong><span>Người dùng</span></article>
        <article class="info-card stat-card-inline"><strong>{{ $stats['courses'] }}</strong><span>Khóa học</span></article>
        <article class="info-card stat-card-inline"><strong>{{ $stats['enrollments'] }}</strong><span>Lượt tham gia</span></article>
        <article class="info-card stat-card-inline"><strong>{{ $stats['announcements'] }}</strong><span>Thông báo đang hiển thị</span></article>
    </section>

    <section class="card-grid card-grid-2 admin-panel-grid">
        <article class="surface-card">
            <div class="section-heading section-heading-inline">
                <div><h2>Đăng ký khóa học mới nhất</h2></div>
            </div>
            <div class="simple-list">
                @forelse ($latestEnrollments as $enrollment)
                    <div class="simple-list__item">
                        <strong>{{ $enrollment->user->name }}</strong>
                        <span>{{ $enrollment->course->title }}</span>
                    </div>
                @empty
                    <p>Chưa có dữ liệu đăng ký.</p>
                @endforelse
            </div>
        </article>
        <article class="surface-card">
            <div class="section-heading section-heading-inline">
                <div><h2>Tài khoản mới</h2></div>
            </div>
            <div class="simple-list">
                @forelse ($recentUsers as $user)
                    <div class="simple-list__item">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                @empty
                    <p>Chưa có tài khoản mới.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="surface-card">
        <div class="section-heading section-heading-inline">
            <div><h2>Khóa học cập nhật gần đây</h2></div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Giảng viên</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($latestCourses as $course)
                        <tr>
                            <td>{{ $course->title }}</td>
                            <td>{{ $course->category->name }}</td>
                            <td>{{ $course->instructor?->name ?? 'Chưa gán' }}</td>
                            <td>{{ $course->is_published ? 'Đang mở' : 'Bản nháp' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection