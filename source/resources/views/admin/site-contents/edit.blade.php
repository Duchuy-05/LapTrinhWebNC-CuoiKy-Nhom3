@extends('layouts.admin')

@section('title', 'Sửa nội dung | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Cập nhật nội dung</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.site-contents.update', $siteContent) }}">
            @csrf
            @method('PUT')
            @include('admin.site-contents._form')
        </form>
    </section>
@endsection