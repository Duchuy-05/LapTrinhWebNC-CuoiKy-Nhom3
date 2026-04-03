<div class="form-grid">
    <div class="form-field form-field-full">
        <label for="title">Tiêu đề</label>
        <input id="title" name="title" type="text" value="{{ old('title', $announcement->title ?? '') }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="body">Nội dung</label>
        <textarea id="body" name="body" rows="6" required>{{ old('body', $announcement->body ?? '') }}</textarea>
    </div>
    <div class="form-field">
        <label for="cta_label">Nhãn nút CTA</label>
        <input id="cta_label" name="cta_label" type="text" value="{{ old('cta_label', $announcement->cta_label ?? '') }}">
    </div>
    <div class="form-field">
        <label for="cta_url">Liên kết CTA</label>
        <input id="cta_url" name="cta_url" type="url" value="{{ old('cta_url', $announcement->cta_url ?? '') }}">
    </div>
    <div class="form-field">
        <label for="starts_at">Bắt đầu hiển thị</label>
        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', isset($announcement?->starts_at) ? $announcement->starts_at->format('Y-m-d\TH:i') : '') }}">
    </div>
    <div class="form-field">
        <label for="ends_at">Kết thúc hiển thị</label>
        <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', isset($announcement?->ends_at) ? $announcement->ends_at->format('Y-m-d\TH:i') : '') }}">
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $announcement->is_active ?? true))>
        <span>Bật thông báo</span>
    </label>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('admin.announcements.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu thông báo</button>
</div>