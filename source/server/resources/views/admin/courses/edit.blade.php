@extends('layouts.admin')

@section('title', 'Sửa Khóa học')

@section('content')
<div class="card card-primary">
    <form action="{{ route('admin.courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Tên khóa học</label>
                    <input type="text" name="title" class="form-control" value="{{ $course->title }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Danh mục</label>
                    <select name="category_id" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $course->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Giá tiền (VNĐ)</label>
                    <input type="number" name="price" class="form-control" value="{{ $course->price }}" min="0" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="draft" {{ $course->status == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                        <option value="published" {{ $course->status == 'published' ? 'selected' : '' }}>Xuất bản</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh thu nhỏ hiện tại</label><br>
                @if($course->thumbnail)
                    <img src="{{ asset($course->thumbnail) }}" width="150" class="mb-2" alt="Thumbnail"><br>
                @endif
                <label>Thay đổi ảnh (Bỏ trống nếu muốn giữ ảnh cũ)</label>
                <input type="file" name="thumbnail" class="form-control-file" accept="image/*">
            </div>

            <div class="form-group">
                <label>Mô tả chi tiết</label>
                <textarea name="description" id="editor" class="form-control" rows="5">{{ $course->description }}</textarea>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Cập nhật thay đổi</button>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-default float-right">Hủy</a>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor');
</script>
@endsection