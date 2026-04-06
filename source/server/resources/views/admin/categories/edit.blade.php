@extends('layouts.admin')

@section('title', 'Sửa Danh mục')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Cập nhật thông tin</h3>
    </div>
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
            </div>
            <div class="form-group">
                <label>Mô tả ngắn</label>
                <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="1" {{ $category->status == 1 ? 'selected' : '' }}>Hiển thị</option>
                    <option value="0" {{ $category->status == 0 ? 'selected' : '' }}>Ẩn</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-default float-right">Hủy</a>
        </div>
    </form>
</div>
@endsection