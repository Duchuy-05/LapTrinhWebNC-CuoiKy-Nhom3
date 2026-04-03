@extends('layouts.instructor')

@section('title', 'Tạo bài kiểm tra | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Tạo bài kiểm tra mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.quizzes.store') }}">
            @csrf
            @include('instructor.quizzes._form')
        </form>
    </section>
@endsection