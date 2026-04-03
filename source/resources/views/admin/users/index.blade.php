@extends('layouts.admin')

@section('title', 'Quản lý người dùng | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Quản lý người dùng</h1></div>
        <a class="button" href="{{ route('admin.users.create') }}">Thêm tài khoản</a>
    </section>

    <section class="surface-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Khóa học đã tham gia</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role === 'admin' ? 'Quản trị viên' : ($user->role === 'instructor' ? 'Giảng viên' : 'Học viên') }}</td>
                            <td>{{ $user->enrollments_count }}</td>
                            <td>{{ $user->is_active ? 'Đang hoạt động' : 'Tạm khóa' }}</td>
                            <td class="table-actions">
                                <a class="text-link" href="{{ route('admin.users.edit', $user) }}">Sửa</a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button-link danger" type="submit">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">@include('partials.pager', ['paginator' => $users])</div>
    </section>
@endsection