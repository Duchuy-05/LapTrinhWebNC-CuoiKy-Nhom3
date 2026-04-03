@extends('layouts.admin')

@section('title', 'Sửa danh mục | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Cập nhật danh mục</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('admin.categories._form')
        </form>
    </section>
@endsection