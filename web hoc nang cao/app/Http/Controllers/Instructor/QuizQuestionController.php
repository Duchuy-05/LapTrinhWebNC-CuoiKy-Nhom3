<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuizQuestionController extends Controller
{
    public function index(Request $request, Quiz $quiz): View
    {
        $quiz = $this->ownedQuiz($request, $quiz);
        $questions = $quiz->questions()->get();

        return view('instructor.questions.index', compact('quiz', 'questions'));
    }

    public function create(Request $request, Quiz $quiz): View
    {
        $quiz = $this->ownedQuiz($request, $quiz);

        return view('instructor.questions.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $quiz = $this->ownedQuiz($request, $quiz);

        $validated = $request->validate([
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:255'],
            'option_b' => ['required', 'string', 'max:255'],
            'option_c' => ['required', 'string', 'max:255'],
            'option_d' => ['required', 'string', 'max:255'],
            'correct_option' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'explanation' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $quiz->questions()->create($validated);

        return redirect()->route('instructor.questions.index', $quiz)->with('status', 'Đã thêm câu hỏi cho bài kiểm tra.');
    }

    public function edit(Request $request, Quiz $quiz, QuizQuestion $question): View
    {
        $quiz = $this->ownedQuiz($request, $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);

        return view('instructor.questions.edit', compact('quiz', 'question'));
    }

    public function update(Request $request, Quiz $quiz, QuizQuestion $question): RedirectResponse
    {
        $quiz = $this->ownedQuiz($request, $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);

        $validated = $request->validate([
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:255'],
            'option_b' => ['required', 'string', 'max:255'],
            'option_c' => ['required', 'string', 'max:255'],
            'option_d' => ['required', 'string', 'max:255'],
            'correct_option' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'explanation' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $question->update($validated);

        return redirect()->route('instructor.questions.index', $quiz)->with('status', 'Đã cập nhật câu hỏi.');
    }

    public function destroy(Request $request, Quiz $quiz, QuizQuestion $question): RedirectResponse
    {
        $quiz = $this->ownedQuiz($request, $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);
        $question->delete();

        return redirect()->route('instructor.questions.index', $quiz)->with('status', 'Đã xóa câu hỏi.');
    }

    private function ownedQuiz(Request $request, Quiz $quiz): Quiz
    {
        abort_unless($quiz->course && $quiz->course->instructor_id === $request->user()->id, 403);

        return $quiz;
    }
}