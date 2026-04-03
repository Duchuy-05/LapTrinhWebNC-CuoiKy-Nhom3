@extends('layouts.instructor')

@section('title', 'Sửa bài kiểm tra | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Cập nhật bài kiểm tra</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.quizzes.update', $quiz) }}">
            @csrf
            @method('PUT')
            @include('instructor.quizzes._form')
        </form>
    </section>
@endsection