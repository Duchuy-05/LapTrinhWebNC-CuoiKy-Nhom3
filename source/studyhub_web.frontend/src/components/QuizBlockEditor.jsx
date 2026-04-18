import React, { useState } from 'react';

const QuizBlockEditor = ({ block, updateBlock }) => {
  const defaultQuestion = { question: '', options: ['', '', '', ''], correctAnswerIndex: 0 };
  const initialData = block.questions || [defaultQuestion];

  const [questions, setQuestions] = useState(initialData);
  const [isPasteMode, setIsPasteMode] = useState(false);
  const [rawText, setRawText] = useState('');

  // YÊU CẦU 1: Hàm đẩy dữ liệu tự động
  const syncToParent = (newQuestions) => {
      setQuestions(newQuestions);
      updateBlock(block.id, { questions: newQuestions });
  };

  const handleQuestionChange = (qIndex, value) => {
    const newQuestions = [...questions];
    newQuestions[qIndex].question = value;
    syncToParent(newQuestions);
  };

  const handleOptionChange = (qIndex, optIndex, value) => {
    const newQuestions = [...questions];
    newQuestions[qIndex].options[optIndex] = value;
    syncToParent(newQuestions);
  };

  const handleCorrectAnswerChange = (qIndex, optIndex) => {
    const newQuestions = [...questions];
    newQuestions[qIndex].correctAnswerIndex = optIndex;
    syncToParent(newQuestions);
  };

  const handleAddQuestion = () => {
    syncToParent([...questions, { question: '', options: ['', '', '', ''], correctAnswerIndex: 0 }]);
  };

  const handleRemoveQuestion = (qIndex) => {
    if (questions.length === 1) return alert("Phải có ít nhất 1 câu hỏi trong bài tập!");
    const newQuestions = questions.filter((_, index) => index !== qIndex);
    syncToParent(newQuestions);
  };

  const handleSmartPaste = () => {
    const lines = rawText.split('\n').map(l => l.trim()).filter(l => l !== '');
    if (lines.length === 0) return alert('Vui lòng dán nội dung vào!');

    const newParsedQuestions = [];
    let currentQuestion = null;
    const questionRegex = /^(?:Câu|Bài|Question)?\s*(\d+)[\.\:\)]?\s*(.*)/i;
    const optionRegex = /^([A-D])[\.\:\)]\s*(.*)/i;

    lines.forEach(line => {
      const qMatch = line.match(questionRegex);
      if (qMatch) {
        if (currentQuestion) newParsedQuestions.push(currentQuestion);
        currentQuestion = { question: qMatch[2] || line, options: ['', '', '', ''], correctAnswerIndex: 0 };
        return; 
      }
      const optMatch = line.match(optionRegex);
      if (optMatch && currentQuestion) {
        const optLetter = optMatch[1].toUpperCase();
        let optIndex = 0;
        if (optLetter === 'A') optIndex = 0; else if (optLetter === 'B') optIndex = 1; else if (optLetter === 'C') optIndex = 2; else if (optLetter === 'D') optIndex = 3;
        currentQuestion.options[optIndex] = optMatch[2];
        return;
      }
      if (currentQuestion && !optMatch) {
        if (!currentQuestion.options.some(opt => opt !== '')) currentQuestion.question += '\n' + line;
      }
    });

    if (currentQuestion) newParsedQuestions.push(currentQuestion);

    if (newParsedQuestions.length > 0) {
      if (window.confirm(`Đã bóc tách được ${newParsedQuestions.length} câu hỏi. OK: Ghi đè. Cancel: Nối thêm.`)) {
        syncToParent(newParsedQuestions);
      } else {
        syncToParent([...questions, ...newParsedQuestions]);
      }
      setIsPasteMode(false);
      setRawText('');
    } else {
      alert("Không tìm thấy cấu trúc câu hỏi hợp lệ!");
    }
  };

  const labels = ['A', 'B', 'C', 'D'];

  return (
    <div className="border border-slate-200 rounded-2xl p-6 bg-white mt-4 shadow-sm relative">

      <div className="flex justify-between items-center mb-6 border-b border-slate-100 pb-4 pt-6">
        <div>
          <h3 className="font-bold text-slate-800 text-lg flex items-center gap-2"><span className="text-2xl">📝</span> Soạn thảo bài tập</h3>
          <p className="text-xs text-slate-400 mt-1">Đang có tổng cộng <span className="font-bold text-blue-500">{questions.length}</span> câu hỏi</p>
        </div>
        <button onClick={() => setIsPasteMode(!isPasteMode)} className={`text-sm font-bold px-4 py-2.5 rounded-xl transition-all duration-300 shadow-sm active:scale-95 cursor-pointer ${isPasteMode ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200'}`}>
          {isPasteMode ? '← Quay lại Form' : '⚡ Nhập nhanh từ Word'}
        </button>
      </div>

      {isPasteMode ? (
        <div className="animate-fadeIn space-y-4 bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-2xl border border-blue-100 shadow-inner">
          <textarea className="w-full h-48 p-4 border-2 border-blue-200 rounded-xl focus:outline-none focus:border-blue-400" placeholder="Dán nội dung vào đây nhé..." value={rawText} onChange={(e) => setRawText(e.target.value)} />
          <button onClick={handleSmartPaste} className="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700">Điền vào form tự động</button>
        </div>
      ) : (
        <div className="animate-fadeIn space-y-8">
          {questions.map((q, qIndex) => (
            <div key={qIndex} className="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm relative group hover:border-blue-200">
              <div className="absolute -top-3 -left-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg transform -rotate-2">Câu {qIndex + 1}</div>
              <button onClick={() => handleRemoveQuestion(qIndex)} className="absolute top-4 right-4 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 p-2 rounded-full cursor-pointer"><svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
              
              <div className="mt-2">
                <div className="mb-5">
                  <label className="text-xs font-bold text-blue-600 mb-2 block uppercase">Nội dung câu hỏi</label>
                  <textarea className="w-full p-4 border-2 border-blue-100 bg-blue-50/40 rounded-xl focus:outline-none focus:border-blue-400 min-h-[80px]" placeholder="Nhập nội dung câu hỏi..." value={q.question} onChange={(e) => handleQuestionChange(qIndex, e.target.value)} />
                </div>
                <div>
                  <label className="text-xs font-bold text-slate-500 mb-3 block uppercase">Các phương án</label>
                  <div className="grid grid-cols-2 gap-4">
                    {q.options.map((opt, optIndex) => (
                      <div key={optIndex} onClick={() => handleCorrectAnswerChange(qIndex, optIndex)} className={`flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer ${q.correctAnswerIndex === optIndex ? 'border-green-400 bg-green-50 shadow-sm' : 'border-slate-100 bg-slate-50'}`}>
                        <input type="radio" checked={q.correctAnswerIndex === optIndex} readOnly className="w-5 h-5 text-green-500 border-slate-300" />
                        <span className={`font-bold text-sm ${q.correctAnswerIndex === optIndex ? 'text-green-600' : 'text-slate-400'}`}>{labels[optIndex]}.</span>
                        <input type="text" className="flex-1 p-1 border-none focus:ring-0 text-sm outline-none bg-transparent" placeholder={`Nhập đáp án ${labels[optIndex]}...`} value={opt} onClick={(e) => e.stopPropagation()} onChange={(e) => handleOptionChange(qIndex, optIndex, e.target.value)} />
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          ))}
          <button onClick={handleAddQuestion} className="cursor-pointer w-full py-4 border-2 border-dashed border-slate-300 rounded-2xl text-slate-500 font-bold hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600">
            <span className="text-xl">+</span> THÊM CÂU HỎI MỚI
          </button>
        </div>
      )}
    </div>
  );
};

export default QuizBlockEditor;