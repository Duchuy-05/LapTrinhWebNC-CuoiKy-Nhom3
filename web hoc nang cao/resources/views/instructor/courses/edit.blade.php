@extends('layouts.instructor')

@section('title', 'Chỉnh sửa khóa học | StudyHub')

@section('content')
    <section class="portal-header">
        <div><span class="eyebrow">Giảng viên</span><h1>Chỉnh sửa khóa học của bạn</h1></div>
    </section>

    <section class="surface-card">
        <form method="POST" action="{{ route('instructor.courses.update', $course) }}" class="form-grid">
            @csrf
            @method('PUT')
            <div class="form-field form-field-full">
                <label for="title">Tiêu đề khóa học</label>
                <input id="title" name="title" type="text" value="{{ old('title', $course->title) }}" required>
            </div>
            <div class="form-field form-field-full">
                <label for="short_description">Mô tả ngắn</label>
                <textarea id="short_description" name="short_description" rows="3" required>{{ old('short_description', $course->short_description) }}</textarea>
            </div>
            <div class="form-field form-field-full">
                <label for="description">Mô tả chi tiết</label>
                <textarea id="description" name="description" rows="8" required>{{ old('description', $course->description) }}</textarea>
            </div>
            <div class="form-field">
                <label for="thumbnail">Ảnh đại diện (URL)</label>
                <input id="thumbnail" name="thumbnail" type="url" value="{{ old('thumbnail', $course->thumbnail) }}">
            </div>
            <div class="form-field">
                <label for="level">Trình độ</label>
                <input id="level" name="level" type="text" value="{{ old('level', $course->level) }}" required>
            </div>
            <div class="form-field">
                <label for="duration_minutes">Thời lượng (phút)</label>
                <input id="duration_minutes" name="duration_minutes" type="number" min="0" value="{{ old('duration_minutes', $course->duration_minutes) }}" required>
            </div>
            <label class="checkbox-field">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $course->is_published))>
                <span>Cho phép hiển thị khóa học trên website</span>
            </label>
            <div class="button-row align-end">
                <a class="button button-ghost" href="{{ route('instructor.courses.index') }}">Quay lại</a>
                <button class="button" type="submit">Lưu thay đổi</button>
            </div>
        </form>
    </section>
@endsection