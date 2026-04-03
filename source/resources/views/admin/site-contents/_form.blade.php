<div class="form-grid">
    <div class="form-field">
        <label for="key">Mã nội dung</label>
        <input id="key" name="key" type="text" value="{{ old('key', $siteContent->key ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="title">Tiêu đề</label>
        <input id="title" name="title" type="text" value="{{ old('title', $siteContent->title ?? '') }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="summary">Tóm tắt</label>
        <textarea id="summary" name="summary" rows="3">{{ old('summary', $siteContent->summary ?? '') }}</textarea>
    </div>
    <div class="form-field form-field-full">
        <label for="body">Nội dung</label>
        <textarea id="body" name="body" rows="10" required>{{ old('body', $siteContent->body ?? '') }}</textarea>
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $siteContent->is_published ?? true))>
        <span>Công khai nội dung này trên website</span>
    </label>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('admin.site-contents.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu nội dung</button>
</div>