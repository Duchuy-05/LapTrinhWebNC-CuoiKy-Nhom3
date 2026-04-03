@extends('layouts.instructor')

@section('title', 'Sửa bài đăng | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Cập nhật bài đăng</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.posts.update', $post) }}">
            @csrf
            @method('PUT')
            @include('instructor.posts._form')
        </form>
    </section>
@endsection