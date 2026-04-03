@extends('layouts.admin')

@section('title', 'Thêm thông báo | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Quản trị</span><h1>Thêm thông báo mới</h1></div>
    </section>
    <section class="surface-card">
        <form method="POST" action="{{ route('admin.announcements.store') }}">
            @csrf
            @include('admin.announcements._form')
        </form>
    </section>
@endsection