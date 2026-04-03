@extends('layouts.admin')

@section('title', 'Thêm khóa học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Thêm khóa học mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.courses.store') }}">
            @csrf
            @include('admin.courses._form')
        </form>
    </section>
@endsection