<div class="form-grid">
    <div class="form-field">
        <label for="name">Tên danh mục</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category->name ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="slug">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $category->slug ?? '') }}" placeholder="Tự động tạo từ tên nếu bỏ trống">
    </div>
    <div class="form-field form-field-full">
        <label for="description">Mô tả</label>
        <textarea id="description" name="description" rows="5">{{ old('description', $category->description ?? '') }}</textarea>
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
        <span>Hiển thị danh mục này trên website</span>
    </label>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('admin.categories.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu danh mục</button>
</div>