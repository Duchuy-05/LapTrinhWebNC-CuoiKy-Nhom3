@extends('layouts.admin')

@section('title', 'Thêm nội dung | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Thêm nội dung mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.site-contents.store') }}">
            @csrf
            @include('admin.site-contents._form')
        </form>
    </section>
@endsection