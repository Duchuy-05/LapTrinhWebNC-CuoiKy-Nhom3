@extends('layouts.admin')

@section('title', 'Thêm Khóa học Mới')

@section('content')
<div class="card card-primary">
    <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Tên khóa học</label>
                    <input type="text" name="title" class="form-control" placeholder="VD: Lập trình PHP từ số 0" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Danh mục</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Chọn danh mục --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Giá tiền (VNĐ)</label>
                    <input type="number" name="price" class="form-control" value="0" min="0" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="draft">Bản nháp (Chưa bán)</option>
                        <option value="published">Xuất bản (Đang bán)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh thu nhỏ (Thumbnail)</label>
                <input type="file" name="thumbnail" class="form-control-file" accept="image/*">
                <small class="form-text text-muted">Chọn ảnh định dạng JPG, PNG. Khuyên dùng kích thước 16:9.</small>
            </div>

            <div class="form-group">
                <label>Mô tả chi tiết</label>
                <textarea name="description" id="editor" class="form-control" rows="5"></textarea>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Lưu khóa học</button>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-default float-right">Hủy</a>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
<script>
    // Tìm thẻ textarea nào có id="editor" thì biến nó thành trình soạn thảo
    CKEDITOR.replace('editor');
</script>
@endsection