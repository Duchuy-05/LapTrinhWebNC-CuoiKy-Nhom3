@extends('layouts.admin')
@section('title', 'Thêm Danh mục Mới')
@section('content')
<div class="card card-primary">
    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="name" class="form-control" placeholder="VD: Lập trình Web" required>
            </div>
            <div class="form-group">
                <label>Mô tả ngắn</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="1">Hiển thị</option>
                    <option value="0">Ẩn</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Lưu danh mục</button>
        </div>
    </form>
</div>
@endsection