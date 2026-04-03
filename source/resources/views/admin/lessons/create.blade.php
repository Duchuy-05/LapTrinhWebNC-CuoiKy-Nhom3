@extends('layouts.admin')

@section('title', 'Thêm bài học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Thêm bài học mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.lessons.store') }}">
            @csrf
            @include('admin.lessons._form')
        </form>
    </section>
@endsection