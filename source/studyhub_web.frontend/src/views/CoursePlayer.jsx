import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

export default function CoursePlayer() {
  const { courseId } = useParams();
  const navigate = useNavigate();
  const [course, setCourse] = useState(null);
  const [activeItem, setActiveItem] = useState(null);
  const [accessMode, setAccessMode] = useState('loading'); 
  const [completedLessons, setCompletedLessons] = useState([]);
  const [quizAnswers, setQuizAnswers] = useState({});
  const [quizSubmitted, setQuizSubmitted] = useState({});
  const [allowedLessonIds, setAllowedLessonIds] = useState([]);

  useEffect(() => {
    const fetchContent = async () => {
      try {
        const response = await CourseAPI.getCourseLearningContent(courseId);
        const { data, access, completedLessons } = response.data;
        
        setCourse(data);
        setAccessMode(access);
        setCompletedLessons(completedLessons || []);
        
        // Lưu mảng bài học được phép xem nếu ở chế độ học thử
        if (access === 'trial' && data.allowedLessonIds) {
            setAllowedLessonIds(data.allowedLessonIds);
        }
      } catch (err) {
        console.error("Lỗi tải nội dung học:", err);
      }
    };
    fetchContent();
  }, [courseId]);

  const getYouTubeVideoId = (url) => {
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
  };

  const handleSelectAnswer = (blockId, qIndex, oIndex) => {
    if (quizSubmitted[blockId]) return; // Đã nộp thì không cho sửa
    setQuizAnswers(prev => ({
      ...prev,
      [blockId]: {
        ...(prev[blockId] || {}),
        [qIndex]: oIndex
      }
    }));
  };

  const handleSubmitQuiz = (blockId) => {
    setQuizSubmitted(prev => ({ ...prev, [blockId]: true }));
  };

  const handleMarkAsComplete = async () => {
    if (!activeItem) return;
    try {
      const response = await CourseAPI.updateProgress(courseId, activeItem.id);
      if (response.data.success) {
        setCompletedLessons(response.data.completedLessons);
        handleNextLesson(); 
      }
    } catch (err) {
      console.error("Lỗi cập nhật tiến độ:", err);
    }
  };

  const allLessons = course?.courseData?.flatMap(unit => unit.items || []) || [];
  const currentLessonIndex = allLessons.findIndex(item => item.id === activeItem?.id);
  const isCourseCompleted = allLessons.length > 0 && completedLessons.length === allLessons.length;

  const handlePrevLesson = () => {
    if (currentLessonIndex > 0) {
      setActiveItem(allLessons[currentLessonIndex - 1]);
    }
  };

  const handleNextLesson = () => {
    if (currentLessonIndex < allLessons.length - 1) {
      setActiveItem(allLessons[currentLessonIndex + 1]);
    }
  };

  if (!course) return <div className="p-10 text-center text-slate-500 font-medium">Đang chuẩn bị bài học...</div>;

  return (
    <div className="flex h-screen bg-slate-50">
      {/* CỘT TRÁI: Danh sách bài học */}
      <div className="w-80 border-r flex flex-col bg-white shadow-sm z-10">
        <div className="p-5 border-b flex flex-col gap-2">
           <div className="font-bold text-lg text-slate-800 leading-tight mt-2">
             {course.title || "Nội dung khóa học"}
             {accessMode === 'trial' && (
               <span className="ml-2 align-middle text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full uppercase tracking-wider">Học thử</span>
             )}
           </div>
        </div>
        <div className="flex-1 overflow-y-auto p-3">
          {course.courseData?.map(unit => (
            <div key={unit.id} className="mb-4">
              <div className="px-3 py-2 bg-slate-100/50 rounded-lg text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                {unit.title}
              </div>
              {unit.items.map(item => {
                // Kiểm tra xem bài này có bị khóa không
                const isLocked = accessMode === 'trial' && !allowedLessonIds.includes(item.id);

                return (
                  <div 
                    key={item.id}
                    onClick={() => setActiveItem(item)}
                    className={`px-4 py-3 text-sm cursor-pointer rounded-xl transition-all mb-1 flex items-center gap-3 ${activeItem?.id === item.id ? 'bg-blue-50 text-blue-700 font-bold shadow-sm ring-1 ring-blue-200' : 'hover:bg-slate-50 text-slate-600'} ${isLocked ? 'opacity-60' : ''}`}
                  >
                    <span className={`${activeItem?.id === item.id ? 'text-blue-500' : 'text-slate-300'}`}>
                      {isLocked ? '🔒' : (completedLessons.includes(item.id) ? '✅' : '▶️')}
                    </span>
                    <span className="flex-1">{item.title}</span>
                  </div>
                );
              })}
            </div>
          ))}
        </div>
      </div>

      {/* CỘT PHẢI: Nội dung chính */}
      <div className="flex-1 overflow-y-auto bg-slate-50 flex flex-col relative">
        {accessMode === 'trial' && (
          <div className="bg-gradient-to-r from-orange-500 to-orange-400 text-white p-3 text-center text-sm font-medium flex justify-center items-center gap-4 shadow-md z-20 sticky top-0">
            <span>Bạn đang xem chế độ <b>Học thử</b>. Đăng ký để mở khóa toàn bộ bài giảng và bài tập!</span>
          </div>
        )}

        <div className="max-w-4xl mx-auto w-full p-10 pb-32">
          {/* --- BANNER CHÚC MỪNG HOÀN THÀNH KHÓA HỌC --- */}
          {isCourseCompleted && (
            <div className="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-6 rounded-2xl shadow-lg shadow-green-200 mb-8 flex flex-col sm:flex-row items-center justify-between gap-4 animate-fade-in-down">
              <div className="flex items-center gap-4">
                <div>
                  <p className="text-green-50 text-sm md:text-base">Bạn đã hoàn thành xuất sắc 100% nội dung của khóa học này.</p>
                </div>
              </div>
              <button 
                onClick={() => navigate('/student/home')}
                className="whitespace-nowrap px-6 py-3 bg-white text-green-700 font-bold rounded-xl shadow-sm hover:bg-green-50 active:scale-95 transition-all cursor-pointer"
              >
                Về trang chủ
              </button>
            </div>
          )}
          {/* ------------------------------------------- */}
          <h1 className="text-3xl font-black text-slate-800 mb-8 border-b border-slate-200 pb-4">{activeItem?.title}</h1>
          
          <div className="space-y-10">
            {/* KIỂM TRA KHÓA BÀI HỌC Ở ĐÂY */}
            {accessMode === 'trial' && activeItem && !allowedLessonIds.includes(activeItem.id) ? (
                <div className="flex flex-col items-center justify-center p-12 bg-white rounded-2xl shadow-sm border border-slate-100 text-center">
                    <span className="text-6xl mb-4">🔒</span>
                    <h3 className="text-xl font-bold text-slate-800 mb-2">Bài học đã bị khóa</h3>
                    <p className="text-slate-500 mb-6">Bạn cần đăng ký khóa học để mở khóa toàn bộ nội dung bài giảng và bài tập.</p>
                    <button onClick={() => navigate(`/student/courses/${courseId}`)} className="px-6 py-3 bg-orange-500 text-white font-bold rounded-xl hover:bg-orange-600 transition-all cursor-pointer shadow-md">
                        Đăng ký ngay
                    </button>
                </div>
            ) : (
              <>
              {(!course.blocks || !course.blocks[activeItem?.id] || course.blocks[activeItem?.id].length === 0) && (
                <div className="text-slate-400 italic bg-white p-6 rounded-xl border border-slate-200 text-center">
                  Bài học này hiện chưa có nội dung.
                </div>
              )}

              {(course.blocks?.[activeItem?.id] || []).map((block) => (
                <div key={block.id} className="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                  
                  {/* 1. HIỂN THỊ VĂN BẢN */}
                  {block.type === 'text' && (
                    <div className="prose prose-slate max-w-none whitespace-pre-wrap text-slate-700 leading-relaxed">
                      {block.content}
                    </div>
                  )}

                  {/* 2. HIỂN THỊ HÌNH ẢNH */}
                  {block.type === 'image' && block.content && (
                    <img src={block.content} alt="Lesson illustration" className="w-full max-h-[500px] object-contain rounded-xl border border-slate-100" />
                  )}

                  {/* 3. HIỂN THỊ VIDEO */}
                  {block.type === 'video' && (
                    <div className="aspect-video bg-black rounded-xl overflow-hidden shadow-md relative">
                      {block.videoType === 'link' ? (
                          getYouTubeVideoId(block.youtubeUrl || block.url) ? (
                            <iframe
                              className="absolute top-0 left-0 w-full h-full"
                              src={`https://www.youtube.com/embed/${getYouTubeVideoId(block.youtubeUrl || block.url)}`}
                              allowFullScreen
                            ></iframe>
                          ) : (
                            <div className="flex items-center justify-center h-full text-slate-400">Video không khả dụng</div>
                          )
                      ) : (
                          <video src={block.uploadUrl || block.url} controls className="w-full h-full object-contain" />
                      )}
                    </div>
                  )}

                  {/* 4. HIỂN THỊ BÀI TẬP TRẮC NGHIỆM */}
                  {block.type === 'quiz' && block.questions && (
                    <div>
                      <h4 className="font-bold text-lg text-slate-800 mb-6 flex items-center gap-2 border-b pb-4">
                        <span className="text-2xl">📝</span> Kiểm tra kiến thức
                      </h4>
                      <div className="space-y-6">
                        {block.questions.map((q, qIdx) => {
                          const isSubmitted = quizSubmitted[block.id];
                          const selectedAnswer = quizAnswers[block.id]?.[qIdx];
                          return (
                            <div key={qIdx} className="bg-slate-50 p-6 rounded-xl border border-slate-200">
                              <p className="font-bold text-slate-800 mb-4">
                                <span className="text-blue-600 mr-1">Câu {qIdx + 1}:</span> {q.question}
                              </p>
                              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {q.options.map((opt, oIdx) => {
                                  let btnClass = "bg-white border-slate-200 text-slate-600 hover:border-blue-300 hover:bg-blue-50";
                                  
                                  if (isSubmitted) {
                                    if (oIdx === q.correctAnswerIndex) {
                                      btnClass = "bg-green-100 border-green-400 text-green-800 font-bold";
                                    } else if (selectedAnswer === oIdx) {
                                      btnClass = "bg-red-50 border-red-400 text-red-600 line-through";
                                    } else {
                                      btnClass = "bg-white border-slate-200 text-slate-400 opacity-50 cursor-not-allowed";
                                    }
                                  } else if (selectedAnswer === oIdx) {
                                    btnClass = "bg-blue-50 border-blue-400 text-blue-700 font-bold shadow-sm ring-2 ring-blue-100";
                                  }

                                  return (
                                    <button 
                                      key={oIdx}
                                      onClick={() => handleSelectAnswer(block.id, qIdx, oIdx)}
                                      disabled={isSubmitted}
                                      className={`p-3 text-left rounded-lg border-2 transition-all duration-200 cursor-pointer ${btnClass}`}
                                    >
                                      <span className="mr-2 font-bold">{['A', 'B', 'C', 'D'][oIdx]}.</span> {opt}
                                      {isSubmitted && oIdx === q.correctAnswerIndex && <span className="float-right">✅</span>}
                                      {isSubmitted && selectedAnswer === oIdx && oIdx !== q.correctAnswerIndex && <span className="float-right">❌</span>}
                                    </button>
                                  );
                                })}
                              </div>
                            </div>
                          );
                        })}
                      </div>
                      
                      {!quizSubmitted[block.id] ? (
                        <button 
                          onClick={() => handleSubmitQuiz(block.id)}
                          className="mt-6 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md transition-all active:scale-95 cursor-pointer"
                        >
                          Kiểm tra
                        </button>
                      ) : (
                        <div></div>
                      )}
                    </div>
                  )}
                </div>
              ))}
              </>
            )}
          </div>
        </div>

        {/* FOOTER ĐIỀU HƯỚNG VÀ HOÀN THÀNH */}
        <div className="mt-12 pt-8 border-t border-slate-200 flex justify-between items-center px-10 pb-10">
          
          {/* NÚT BÀI TRƯỚC */}
          <button 
            onClick={handlePrevLesson}
            disabled={currentLessonIndex <= 0}
            className={`px-6 py-2 font-bold transition-colors ${currentLessonIndex <= 0 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-500 hover:text-blue-600 cursor-pointer'}`}
          >
            ← Bài trước
          </button>
          
          {/* KHU VỰC NÚT HOÀN THÀNH / BÀI TIẾP THEO */}
          {accessMode === 'full' ? (
            !completedLessons.includes(activeItem?.id) ? (
              <button 
                onClick={handleMarkAsComplete}
                className="px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 shadow-lg shadow-green-100 transition-all active:scale-95 cursor-pointer"
              >
                Hoàn thành bài học
              </button>
            ) : (
              <div className="flex items-center gap-2 text-green-600 font-bold">
                <span>✔ Đã hoàn thành</span>
                
                {/* NÚT BÀI TIẾP THEO */}
                <button 
                  onClick={handleNextLesson}
                  disabled={currentLessonIndex >= allLessons.length - 1}
                  className={`px-8 py-3 font-bold rounded-xl ml-4 transition-all ${currentLessonIndex >= allLessons.length - 1 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700 active:scale-95 cursor-pointer'}`}
                >
                  Bài tiếp theo →
                </button>
              </div>
            )
          ) : (
            <div className="text-orange-600 font-medium italic bg-orange-50 px-4 py-2 rounded-lg border border-orange-200">
              Đăng ký khóa học để lưu tiến độ
            </div>
          )}
        </div>
      </div>
    </div>
  );
}