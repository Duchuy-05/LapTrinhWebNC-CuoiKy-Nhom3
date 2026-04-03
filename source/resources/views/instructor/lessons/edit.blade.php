@extends('layouts.instructor')

@section('title', 'Sửa bài học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Cập nhật bài học</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.lessons.update', $lesson) }}">
            @csrf
            @method('PUT')
            @include('instructor.lessons._form')
        </form>
    </section>
@endsection