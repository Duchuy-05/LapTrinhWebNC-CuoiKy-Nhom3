@extends('layouts.instructor')

@section('title', 'Thêm câu hỏi | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Thêm câu hỏi cho bài kiểm tra</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.questions.store', $quiz) }}">
            @csrf
            @include('instructor.questions._form')
        </form>
    </section>
@endsection