@extends('layouts.admin')

@section('title', 'Sửa bài học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Cập nhật bài học</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}">
            @csrf
            @method('PUT')
            @include('admin.lessons._form')
        </form>
    </section>
@endsection