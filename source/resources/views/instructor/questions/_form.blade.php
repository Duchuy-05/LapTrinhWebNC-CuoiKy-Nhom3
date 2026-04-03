<div class="form-grid">
    <div class="form-field form-field-full">
        <label for="question">Nội dung câu hỏi</label>
        <textarea id="question" name="question" rows="4" required>{{ old('question', $question->question ?? '') }}</textarea>
    </div>
    <div class="form-field">
        <label for="option_a">Đáp án A</label>
        <input id="option_a" name="option_a" type="text" value="{{ old('option_a', $question->option_a ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="option_b">Đáp án B</label>
        <input id="option_b" name="option_b" type="text" value="{{ old('option_b', $question->option_b ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="option_c">Đáp án C</label>
        <input id="option_c" name="option_c" type="text" value="{{ old('option_c', $question->option_c ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="option_d">Đáp án D</label>
        <input id="option_d" name="option_d" type="text" value="{{ old('option_d', $question->option_d ?? '') }}" required>
    </div>
    <div class="form-field">
        <label for="correct_option">Đáp án đúng</label>
        <select id="correct_option" name="correct_option" required>
            @foreach (['A', 'B', 'C', 'D'] as $option)
                <option value="{{ $option }}" @selected(old('correct_option', $question->correct_option ?? 'A') === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="sort_order">Thứ tự</label>
        <input id="sort_order" name="sort_order" type="number" min="1" value="{{ old('sort_order', $question->sort_order ?? 1) }}" required>
    </div>
    <div class="form-field form-field-full">
        <label for="explanation">Giải thích đáp án</label>
        <textarea id="explanation" name="explanation" rows="4">{{ old('explanation', $question->explanation ?? '') }}</textarea>
    </div>
</div>
<div class="button-row align-end">
    <a class="button button-ghost" href="{{ route('instructor.questions.index', $quiz) }}">Hủy</a>
    <button class="button" type="submit">Lưu câu hỏi</button>
</div>