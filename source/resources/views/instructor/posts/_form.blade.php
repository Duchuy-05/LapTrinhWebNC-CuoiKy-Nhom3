<div class="form-grid">
    <div class="form-field">
        <label for="course_id">Đăng cho khóa học</label>
        <select id="course_id" name="course_id" required>
            @foreach ($courses as $courseOption)
                <option value="{{ $courseOption->id }}" @selected(old('course_id', $post->course_id ?? '') == $courseOption->id)>{{ $courseOption->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field form-field-full">
        <label for="title">Tiêu đề bài đăng</label>
        <input id="title" name="title" type="text" value="{{ old('title', $post->title ?? '') }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="excerpt">Tóm tắt ngắn</label>
        <textarea id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
    </div>
    <div class="form-field form-field-full">
        <label for="body">Nội dung bài đăng</label>
        <textarea id="body" name="body" rows="8" required>{{ old('body', $post->body ?? '') }}</textarea>
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $post->is_published ?? true))>
        <span>Hiển thị bài đăng này cho học viên</span>
    </label>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('instructor.posts.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu bài đăng</button>
</div>