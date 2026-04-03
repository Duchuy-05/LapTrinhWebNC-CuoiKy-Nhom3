@extends('layouts.admin')

@section('title', 'Sửa người dùng | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Cập nhật tài khoản</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form')
        </form>
    </section>
@endsection