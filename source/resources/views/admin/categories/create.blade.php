@extends('layouts.admin')

@section('title', 'Thêm danh mục | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Thêm danh mục mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            @include('admin.categories._form')
        </form>
    </section>
@endsection