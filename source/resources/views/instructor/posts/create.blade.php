@extends('layouts.instructor')

@section('title', 'Đăng bài mới | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Tạo bài đăng mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.posts.store') }}">
            @csrf
            @include('instructor.posts._form')
        </form>
    </section>
@endsection