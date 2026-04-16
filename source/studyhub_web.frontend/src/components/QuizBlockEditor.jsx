import React, { useState } from 'react';

// Component con chuyên biệt để soạn thảo bài tập trắc nghiệm
const QuizBlockEditor = ({ block, updateBlock }) => {
  const defaultQuestion = { question: '', options: ['', '', '', ''], correctAnswerIndex: 0 };
  const initialData = block.questions || [defaultQuestion];

  const [questions, setQuestions] = useState(initialData);
  const [isPasteMode, setIsPasteMode] = useState(false);
  const [rawText, setRawText] = useState('');

  // Hàm xử lý thay đổi nội dung câu hỏi
  const handleQuestionChange = (qIndex, value) => {
    const newQuestions = [...questions];
    newQuestions[qIndex].question = value;
    setQuestions(newQuestions);
  };

  // Hàm xử lý thay đổi nội dung đáp án
  const handleOptionChange = (qIndex, optIndex, value) => {
    const newQuestions = [...questions];
    newQuestions[qIndex].options[optIndex] = value;
    setQuestions(newQuestions);
  };

  // Hàm xử lý thay đổi đáp án đúng
  const handleCorrectAnswerChange = (qIndex, optIndex) => {
    const newQuestions = [...questions];
    newQuestions[qIndex].correctAnswerIndex = optIndex;
    setQuestions(newQuestions);
  };

  // Hàm thêm câu hỏi mới
  const handleAddQuestion = () => {
    setQuestions([...questions, { question: '', options: ['', '', '', ''], correctAnswerIndex: 0 }]);
  };

  // Hàm xóa câu hỏi
  const handleRemoveQuestion = (qIndex) => {
    if (questions.length === 1) return alert("Phải có ít nhất 1 câu hỏi trong bài tập!");
    const newQuestions = questions.filter((_, index) => index !== qIndex);
    setQuestions(newQuestions);
  };

  // Hàm xử lý dán nội dung từ Word/PDF
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
        currentQuestion = {
          question: qMatch[2] || line, 
          options: ['', '', '', ''],
          correctAnswerIndex: 0
        };
        return; 
      }

      const optMatch = line.match(optionRegex);
      if (optMatch && currentQuestion) {
        const optLetter = optMatch[1].toUpperCase();
        const optText = optMatch[2];
        
        let optIndex = 0;
        if (optLetter === 'A') optIndex = 0;
        else if (optLetter === 'B') optIndex = 1;
        else if (optLetter === 'C') optIndex = 2;
        else if (optLetter === 'D') optIndex = 3;

        currentQuestion.options[optIndex] = optText;
        return;
      }

      if (currentQuestion && !optMatch) {
        const hasOptions = currentQuestion.options.some(opt => opt !== '');
        if (!hasOptions) {
            currentQuestion.question += '\n' + line;
        }
      }
    });

    if (currentQuestion) newParsedQuestions.push(currentQuestion);

    if (newParsedQuestions.length > 0) {
      const confirmOverride = window.confirm(`Đã bóc tách được ${newParsedQuestions.length} câu hỏi. Bạn muốn ghi đè (OK) hay Nối thêm vào cuối (Cancel)?`);
      if (confirmOverride) {
        setQuestions(newParsedQuestions);
      } else {
        setQuestions([...questions, ...newParsedQuestions]);
      }
      setIsPasteMode(false);
      setRawText('');
      alert("Đã nhập dữ liệu thành công!");
    } else {
      alert("Không tìm thấy cấu trúc câu hỏi (1. A. B. C. D.) hợp lệ trong đoạn văn bản!");
    }
  };

  const handleSave = () => {
    for (let i = 0; i < questions.length; i++) {
      if (!questions[i].question.trim()) return alert(`Vui lòng nhập nội dung cho câu hỏi số ${i + 1}`);
    }
    updateBlock(block.id, { questions: questions });
    alert(`Đã lưu ${questions.length} câu hỏi trắc nghiệm!`);
  };

  const labels = ['A', 'B', 'C', 'D'];

  return (
    <div className="border border-slate-200 rounded-2xl p-6 bg-white mt-4 shadow-sm transition-all">
      {/* Header Tool */}
      <div className="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
        <div>
          <h3 className="font-bold text-slate-800 text-lg flex items-center gap-2">
            <span className="text-2xl">📝</span> Soạn thảo bài tập
          </h3>
          <p className="text-xs text-slate-400 mt-1">Đang có tổng cộng <span className="font-bold text-blue-500">{questions.length}</span> câu hỏi</p>
        </div>
        <button 
          onClick={() => setIsPasteMode(!isPasteMode)}
          className={`text-sm font-bold px-4 py-2.5 rounded-xl transition-all duration-300 shadow-sm active:scale-95 cursor-pointer ${isPasteMode ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 hover:border-blue-300 '}`}
        >
          {isPasteMode ? '← Quay lại Form nhập' : '⚡ Nhập nhanh từ Word/PDF'}
        </button>
      </div>

      {isPasteMode ? (
        <div className="animate-fadeIn space-y-4 bg-gradient-to-br from-blue-50 to-indigo-50/50 p-5 rounded-2xl border border-blue-100 shadow-inner">
          <p className="text-sm font-bold text-blue-800">Dán danh sách câu hỏi và đáp án từ Word/PDF vào đây:</p>
          <div className="text-xs text-slate-600 bg-white/80 p-3 rounded-xl border border-blue-100 shadow-sm">
            <p className="font-bold text-slate-800 mb-1">💡 Cấu trúc chuẩn để hệ thống nhận dạng:</p>
            1. Thủ đô của Việt Nam là?<br/>
            A. Hà Nội<br/>
            B. Đà Nẵng<br/>
            C. Hồ Chí Minh<br/>
            D. Huế<br/><br/>
          </div>
          <textarea 
            className="w-full h-48 p-4 border-2 border-blue-200 rounded-xl focus:outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all text-sm bg-white shadow-sm resize-none"
            placeholder="Dán nội dung vào đây nhé..."
            value={rawText}
            onChange={(e) => setRawText(e.target.value)}
          />
          <button 
            onClick={handleSmartPaste}
            className="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md hover:shadow-lg transition-all active:scale-95 cursor-pointer"
          >
            Điền vào form tự động
          </button>
        </div>
      ) : (
        <div className="animate-fadeIn space-y-8">
          
          {questions.map((q, qIndex) => (
            <div key={qIndex} className="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm relative group transition-all duration-300 hover:shadow-md hover:border-blue-200">
              
              {/* Badge Câu hỏi */}
              <div className="absolute -top-3 -left-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md transform -rotate-2">
                Câu {qIndex + 1}
              </div>
              
              {/* Nút Xóa */}
              <button 
                onClick={() => handleRemoveQuestion(qIndex)}
                className="absolute top-4 right-4 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 hover:bg-red-50 p-2 rounded-full cursor-pointer"
                title="Xóa câu hỏi này"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>

              <div className="mt-2">
                {/* ---------------- Ô CÂU HỎI  ---------------- */}
                <div className="mb-5">
                  <label className="text-xs font-bold text-blue-600 mb-2 flex items-center gap-1 uppercase tracking-wider">
                     Nội dung câu hỏi
                  </label>
                  <textarea 
                    className="w-full p-4 border-2 border-blue-100 bg-blue-50/40 rounded-xl focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-50 transition-all duration-300 min-h-[80px] text-slate-700 shadow-inner hover:border-blue-200"
                    placeholder="Nhập nội dung câu hỏi..."
                    value={q.question}
                    onChange={(e) => handleQuestionChange(qIndex, e.target.value)}
                  />
                </div>

                {/* ---------------- Ô ĐÁP ÁN  ---------------- */}
            <div>
                  <label className="text-xs font-bold text-slate-500 mb-3 flex items-center gap-2 uppercase tracking-wider">
                    Các phương án <span className="text-[10px] font-normal text-slate-400 normal-case bg-slate-100 px-2 py-0.5 rounded-full">(Click vào ô để chọn đáp án đúng)</span>
                  </label>
                  
                  <div className="grid grid-cols-2 gap-4">
                    {q.options.map((opt, optIndex) => (
                      <div 
                        key={optIndex} 
                        onClick={() => handleCorrectAnswerChange(qIndex, optIndex)} // Thêm onClick vào cả div
                        className={`flex items-center gap-3 p-3 rounded-xl border-2 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-md cursor-pointer ${
                          q.correctAnswerIndex === optIndex 
                            ? 'border-green-400 bg-green-50/80 shadow-sm' 
                            : 'border-slate-100 bg-slate-50 hover:border-slate-300 hover:bg-white'
                        }`}
                      >
                        <input 
                          type="radio" 
                          name={`correctAnswer-${block.id}-${qIndex}`}
                          checked={q.correctAnswerIndex === optIndex}
                          readOnly // Để div cha xử lý click
                          className="w-5 h-5 text-green-500 border-slate-300 focus:ring-green-500 cursor-pointer transition-transform hover:scale-110"
                        />
                        
                        <span className={`font-bold text-sm transition-colors ${q.correctAnswerIndex === optIndex ? 'text-green-600' : 'text-slate-400'}`}>
                          {labels[optIndex]}.
                        </span>
                        
                        <input 
                          type="text" 
                          className={`flex-1 p-1 border-none focus:ring-0 text-sm outline-none transition-colors w-full ${
                            q.correctAnswerIndex === optIndex ? 'bg-transparent text-green-800 placeholder-green-300' : 'bg-transparent text-slate-700 placeholder-slate-300'
                          }`}
                          placeholder={`Nhập đáp án ${labels[optIndex]}...`}
                          value={opt}
                          onClick={(e) => e.stopPropagation()} // Chặn click nổ lên div khi đang sửa chữ
                          onChange={(e) => handleOptionChange(qIndex, optIndex, e.target.value)}
                        />
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          ))}

          {/* Nút thêm câu hỏi rỗng */}
          <button 
            onClick={handleAddQuestion}
            className="w-full py-4 border-2 border-dashed border-slate-300 rounded-2xl text-slate-500 font-bold hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 transition-all duration-300 flex justify-center items-center gap-2 group"
          >
            <span className="text-xl group-hover:scale-125 transition-transform">+</span> THÊM CÂU HỎI MỚI
          </button>
        </div>
      )}

      {/* Nút thao tác lưu */}
      <div className="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
        <button className="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors active:scale-95 cursor-pointer">
          HỦY BỎ
        </button>
        <button onClick={handleSave} className="px-8 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-md shadow-blue-200 transition-all active:scale-95 flex items-center gap-2 cursor-pointer">
          <span>💾</span> LƯU BÀI TẬP
        </button>
      </div>
    </div>
  );
};

export default QuizBlockEditor;