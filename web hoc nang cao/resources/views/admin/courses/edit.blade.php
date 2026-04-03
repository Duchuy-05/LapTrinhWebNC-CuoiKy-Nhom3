@extends('layouts.admin')

@section('title', 'Sửa khóa học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Cập nhật khóa học</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.courses.update', $course) }}">
            @csrf
            @method('PUT')
            @include('admin.courses._form')
        </form>
    </section>
@endsection