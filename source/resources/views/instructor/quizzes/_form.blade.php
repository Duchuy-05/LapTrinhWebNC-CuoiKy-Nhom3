<div class="form-grid">
    <div class="form-field">
        <label for="course_id">Khóa học</label>
        <select id="course_id" name="course_id" required>
            @foreach ($courses as $courseOption)
                <option value="{{ $courseOption->id }}" @selected(old('course_id', $quiz->course_id ?? '') == $courseOption->id)>{{ $courseOption->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="lesson_id">Gắn với bài học</label>
        <select id="lesson_id" name="lesson_id">
            <option value="">Không gắn bài học cụ thể</option>
            @foreach ($courses as $courseOption)
                @foreach ($courseOption->lessons as $lessonOption)
                    <option value="{{ $lessonOption->id }}" @selected(old('lesson_id', $quiz->lesson_id ?? '') == $lessonOption->id)>{{ $courseOption->title }} · {{ $lessonOption->title }}</option>
                @endforeach
            @endforeach
        </select>
    </div>
    <div class="form-field form-field-full">
        <label for="title">Tên bài kiểm tra</label>
        <input id="title" name="title" type="text" value="{{ old('title', $quiz->title ?? '') }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="description">Mô tả</label>
        <textarea id="description" name="description" rows="5">{{ old('description', $quiz->description ?? '') }}</textarea>
    </div>
    <div class="form-field">
        <label for="passing_score">Điểm đạt (%)</label>
        <input id="passing_score" name="passing_score" type="number" min="1" max="100" value="{{ old('passing_score', $quiz->passing_score ?? 70) }}" required>
    </div>
    <div class="form-field">
        <label for="time_limit_minutes">Giới hạn thời gian (phút)</label>
        <input id="time_limit_minutes" name="time_limit_minutes" type="number" min="1" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes ?? '') }}">
    </div>
    <label class="checkbox-field">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $quiz->is_published ?? true))>
        <span>Công khai bài kiểm tra cho học viên</span>
    </label>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('instructor.quizzes.index') }}">Hủy</a>
    <button class="button" type="submit">Lưu bài kiểm tra</button>
</div>