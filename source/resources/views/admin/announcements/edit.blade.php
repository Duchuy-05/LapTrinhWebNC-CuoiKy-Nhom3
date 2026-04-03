@extends('layouts.admin')

@section('title', 'Sửa thông báo | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Cập nhật thông báo</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}">
            @csrf
            @method('PUT')
            @include('admin.announcements._form')
        </form>
    </section>
@endsection