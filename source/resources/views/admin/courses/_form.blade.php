<div class="form-grid">
    <div class="form-field">
        <label for="category_id">Danh mục</label>
        <select id="category_id" name="category_id" required>
            @foreach ($categories as $categoryOption)
                <option value="{{ $categoryOption->id }}" @selected(old('category_id', $course->category_id ?? '') == $categoryOption->id)>{{ $categoryOption->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="instructor_id">Giảng viên phụ trách</label>
        <select id="instructor_id" name="instructor_id">
            <option value="">Chọn giảng viên</option>
            @foreach ($instructors as $instructor)
                <option value="{{ $instructor->id }}" @selected(old('instructor_id', $course->instructor_id ?? '') == $instructor->id)>{{ $instructor->name }}</option>
            @endforeach
        </select>
        <small>Chỉ quản trị viên mới có thể tạo và phân công tài khoản giảng viên.</small>
    </div>
    <div class="form-field form-field-full">
        <label for="title">Tiêu đề khóa học</label>
        <input id="title" name="title" type="text" value="{{ old('title', $course->title ?? '') }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="slug">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $course->slug ?? '') }}" placeholder="Tự động tạo từ tên nếu bỏ trống">
    </div>
    <div class="form-field form-field-full">
        <label for="short_description">Mô tả ngắn</label>
        <textarea id="short_description" name="short_description" rows="3" required>{{ old('short_description', $course->short_description ?? '') }}</textarea>
    </div>
    <div class="form-field form-field-full">
        <label for="description">Mô tả chi tiết</label>
        <textarea id="description" name="description" rows="8" required>{{ old('description', $course->description ?? '') }}</textarea>
    </div>
    <div class="form-field">
        <label for="thumbnail">Ảnh đại diện (URL)</label>
        <input id="thumbnail" name="thumbnail" type="url" value="{{ old('thumbnail', $course->thumbnail ?? '') }}">
    </div>
    <div class="form-field">
        <label for="level">Trình độ</label>
        <input id="level" name="level" type="text" value="{{ old('level', $course->level ?? 'Cơ bản') }}" required>
    </div>
    <div class="form-field">
        <label for="duration_minutes">Thời lượng (phút)</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="0" value="{{ old('duration_minutes', $course->duration_minutes ?? 0) }}" required>
    </div>
    <div class="form-field">
        <label for="price">Học phí</label>
        <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $course->price ?? 0) }}" required>
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $course->is_featured ?? false))>
        <span>Đánh dấu là khóa học nổi bật</span>
    </label>
    <label class="checkbox-field">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $course->is_published ?? true))>
        <span>Hiển thị khóa học trên website</span>
    </label>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('admin.courses.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu khóa học</button>
</div>