@extends('layouts.instructor')

@section('title', 'Sửa câu hỏi | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Cập nhật câu hỏi</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.questions.update', [$quiz, $question]) }}">
            @csrf
            @method('PUT')
            @include('instructor.questions._form')
        </form>
    </section>
@endsection