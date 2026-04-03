@extends('layouts.instructor')

@section('title', 'Thêm bài học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Thêm bài học mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.lessons.store') }}">
            @csrf
            @include('instructor.lessons._form')
        </form>
    </section>
@endsection