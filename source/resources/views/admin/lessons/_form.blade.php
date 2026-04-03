<div class="form-grid">
    <div class="form-field">
        <label for="course_id">Khóa học</label>
        <select id="course_id" name="course_id" required>
            @foreach ($courses as $courseOption)
                <option value="{{ $courseOption->id }}" @selected(old('course_id', $lesson->course_id ?? '') == $courseOption->id)>{{ $courseOption->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="sort_order">Thứ tự</label>
        <input id="sort_order" name="sort_order" type="number" min="1" value="{{ old('sort_order', $lesson->sort_order ?? 1) }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="title">Tiêu đề bài học</label>
        <input id="title" name="title" type="text" value="{{ old('title', $lesson->title ?? '') }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="slug">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $lesson->slug ?? '') }}">
    </div>
    <div class="form-field form-field-full">
        <label for="excerpt">Mô tả ngắn</label>
        <textarea id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $lesson->excerpt ?? '') }}</textarea>
    </div>
    <div class="form-field form-field-full">
        <label for="content">Nội dung bài học</label>
        <textarea id="content" name="content" rows="8">{{ old('content', $lesson->content ?? '') }}</textarea>
    </div>
    <div class="form-field">
        <label for="video_url">Liên kết video</label>
        <input id="video_url" name="video_url" type="url" value="{{ old('video_url', $lesson->video_url ?? '') }}">
    </div>
    <div class="form-field">
        <label for="document_url">Liên kết tài liệu</label>
        <input id="document_url" name="document_url" type="url" value="{{ old('document_url', $lesson->document_url ?? '') }}">
    </div>
    <div class="form-field">
        <label for="duration_minutes">Thời lượng (phút)</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="0" value="{{ old('duration_minutes', $lesson->duration_minutes ?? 0) }}" required>
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_preview" value="1" @checked(old('is_preview', $lesson->is_preview ?? false))>
        <span>Cho phép học thử bài này</span>
    </label>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('admin.lessons.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu bài học</button>
</div>