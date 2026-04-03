@extends('layouts.admin')

@section('title', 'Thêm người dùng | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Thêm tài khoản mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form')
        </form>
    </section>
@endsection