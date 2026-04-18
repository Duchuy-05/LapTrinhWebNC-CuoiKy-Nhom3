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

  useEffect(() => {
    const fetchContent = async () => {
      try {
        const response = await CourseAPI.getCourseLearningContent(courseId);
        const { data, access, completedLessons } = response.data;
        
        setCourse(data);
        setAccessMode(access);
        setCompletedLessons(completedLessons || []);
        
        // Tự động active bài học ĐẦU TIÊN KHÔNG BỊ KHÓA
        if (data && data.courseData) {
           const allItems = data.courseData.flatMap(unit => unit.items || []);
           // Ưu tiên chọn bài chưa hoàn thành và không bị khóa
           const firstAvailable = allItems.find(item => !item.is_locked) || allItems[0];
           setActiveItem(firstAvailable);
        }
      } catch (err) {
        console.error("Lỗi tải nội dung học:", err);
      }
    };
    fetchContent();
  }, [courseId]);

  const getYouTubeVideoId = (url) => { /* Giữ nguyên hàm của bạn */
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
  };

  const handleSelectAnswer = (blockId, qIndex, oIndex) => { /* Giữ nguyên hàm của bạn */
    if (quizSubmitted[blockId]) return; 
    setQuizAnswers(prev => ({ ...prev, [blockId]: { ...(prev[blockId] || {}), [qIndex]: oIndex } }));
  };

  const handleSubmitQuiz = (blockId) => { setQuizSubmitted(prev => ({ ...prev, [blockId]: true })); };

  const handleMarkAsComplete = async () => { /* Giữ nguyên hàm của bạn */
    if (!activeItem) return;
    try {
      const response = await CourseAPI.updateProgress(courseId, activeItem.id);
      if (response.data.success) {
        setCompletedLessons(response.data.completedLessons);
        handleNextLesson(); 
      }
    } catch (err) { console.error("Lỗi cập nhật tiến độ:", err); }
  };

  const allLessons = course?.courseData?.flatMap(unit => unit.items || []) || [];
  const currentLessonIndex = allLessons.findIndex(item => item.id === activeItem?.id);
  const isCourseCompleted = allLessons.length > 0 && completedLessons.length === allLessons.length;

  const handlePrevLesson = () => { if (currentLessonIndex > 0) setActiveItem(allLessons[currentLessonIndex - 1]); };
  const handleNextLesson = () => { if (currentLessonIndex < allLessons.length - 1) setActiveItem(allLessons[currentLessonIndex + 1]); };

  const handleEnrollClick = async () => {
     try {
         // Chuyển hướng học viên đến trang chi tiết để bấm mua/thanh toán PayOS
         navigate(`/courses/${courseId}`); 
     } catch (error) {
         alert("Lỗi chuyển hướng thanh toán");
     }
  };

  if (!course) return <div className="flex h-screen items-center justify-center bg-slate-50"><div className="animate-pulse flex flex-col items-center"><span className="text-4xl mb-4">📚</span><p className="text-slate-500 font-bold">Đang chuẩn bị lớp học...</p></div></div>;

  return (
    <div className="flex h-screen bg-slate-50 font-sans">
      
      {/* ================= CỘT TRÁI: MENU BÀI HỌC ================= */}
      <div className="w-80 border-r border-slate-200 flex flex-col bg-white shadow-sm z-10">
        <div className="p-5 border-b border-slate-100 flex flex-col gap-2">
           <div className="font-extrabold text-lg text-slate-800 leading-tight mt-2 line-clamp-2">
             {course.title || "Nội dung khóa học"}
           </div>
           {accessMode === 'trial' && (
             <div className="bg-orange-100 border border-orange-200 text-orange-700 px-3 py-1.5 rounded-lg text-xs font-bold mt-2 flex items-center justify-center gap-2 shadow-sm">
               <span>👀 ĐANG HỌC THỬ</span>
             </div>
           )}
        </div>
        
        <div className="flex-1 overflow-y-auto p-3 custom-scrollbar space-y-4">
          {course.courseData?.map(unit => (
            <div key={unit.id} className="mb-2">
              <div className="px-3 py-2 bg-slate-50 border border-slate-100 rounded-lg text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                {unit.title}
              </div>
              <div className="space-y-1">
                {unit.items.map((item, index) => {
                  
                  // Lấy trực tiếp cờ is_locked từ Backend trả về
                  const isLocked = item.is_locked;
                  const isActive = activeItem?.id === item.id;
                  const isCompleted = completedLessons.includes(item.id);

                  return (
                    <div 
                      key={item.id}
                      onClick={() => setActiveItem(item)}
                      className={`px-4 py-3 text-sm cursor-pointer rounded-xl transition-all flex items-center gap-3 border ${
                        isActive 
                          ? 'bg-blue-50 border-blue-200 shadow-sm' 
                          : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-200'
                      } ${isLocked ? 'opacity-70' : ''}`}
                    >
                      {/* Icon trạng thái bài học */}
                      <span className={`flex items-center justify-center w-6 h-6 rounded-full text-[10px] shrink-0 ${
                        isLocked ? 'bg-slate-100 text-slate-400' :
                        isCompleted ? 'bg-green-100 text-green-600' : 
                        isActive ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-500'
                      }`}>
                        {isLocked ? '🔒' : (isCompleted ? '✔' : index + 1)}
                      </span>
                      
                      <span className={`flex-1 line-clamp-2 leading-snug ${isActive ? 'font-bold text-blue-800' : 'font-medium text-slate-700'}`}>
                        {item.title}
                      </span>

                      {/* Hiển thị chữ FREE cho những bài học thử trong lúc khóa học chưa mua */}
                      {accessMode === 'trial' && !isLocked && (
                        <span className="text-[9px] bg-green-500 text-white font-bold px-2 py-0.5 rounded shadow-sm shrink-0">
                          FREE
                        </span>
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* ================= CỘT PHẢI: NỘI DUNG ================= */}
      <div className="flex-1 overflow-y-auto bg-slate-100 flex flex-col relative scroll-smooth">
        
        {/* THANH THÔNG BÁO HỌC THỬ TRÊN CÙNG */}
        {accessMode === 'trial' && (
          <div className="bg-gradient-to-r from-orange-500 to-amber-500 text-white py-3 px-6 text-sm font-medium flex justify-between items-center shadow-md z-20 sticky top-0">
            <span>🚀 Bạn đang trải nghiệm chế độ <b>Học thử</b>. Nội dung cao cấp đã bị khóa.</span>
            <button onClick={handleEnrollClick} className="bg-white text-orange-600 text-xs font-bold px-4 py-1.5 rounded-lg shadow-sm hover:bg-orange-50 transition-colors cursor-pointer">
              Đăng ký Full Khóa Học
            </button>
          </div>
        )}

        <div className="max-w-4xl mx-auto w-full p-10 pb-32">
          
          <h1 className="text-3xl font-black text-slate-800 mb-8 border-b border-slate-200 pb-4">{activeItem?.title}</h1>
          
          <div className="space-y-10">
            
            {/* LƯỚI CHẶN BẢO MẬT: BÀI BỊ KHÓA SẼ HIỆN Ở ĐÂY */}
            {activeItem?.is_locked ? (
                <div className="flex flex-col items-center justify-center p-16 bg-white rounded-3xl shadow-xl border border-slate-200 text-center relative overflow-hidden animate-fadeIn">
                    <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5"></div>
                    <div className="w-24 h-24 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mb-6 shadow-inner z-10">
                        <span className="text-5xl">🔒</span>
                    </div>
                    <h3 className="text-2xl font-black text-slate-800 mb-4 z-10">Bài học này yêu cầu trả phí!</h3>
                    <p className="text-slate-500 mb-8 max-w-md mx-auto leading-relaxed z-10">
                        Vui lòng thanh toán và đăng ký khóa học để tiếp tục mở khóa bài giảng này cùng toàn bộ bài tập thực hành.
                    </p>
                    <button 
                        onClick={handleEnrollClick} 
                        className="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer z-10 flex items-center gap-2"
                    >
                        <span> THANH TOÁN NGAY</span>
                    </button>
                </div>
            ) : (
              /* NẾU ĐƯỢC PHÉP HỌC (FREE HOẶC ĐÃ MUA) THÌ HIỂN THỊ NỘI DUNG */
              <>
                {(!course.blocks || !course.blocks[activeItem?.id] || course.blocks[activeItem?.id].length === 0) && (
                  <div className="text-slate-400 italic bg-white p-12 rounded-2xl border border-dashed border-slate-300 text-center flex flex-col items-center">
                    <span className="text-4xl mb-4 opacity-20">📭</span>
                    <span>Giảng viên chưa cập nhật nội dung cho bài học này.</span>
                  </div>
                )}

                {(course.blocks?.[activeItem?.id] || []).map((block) => (
                  <div key={block.id} className="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 transition-all hover:shadow-md">
                     {/* ===================== GIỮ NGUYÊN CODE RENDER TEXT, VIDEO, QUIZ CỦA BẠN TẠI ĐÂY ===================== */}
                     {/* (Code Render Text, Image, Video, Quiz của bạn đã quá chuẩn nên mình giữ nguyên vẹn ở đây) */}
                     
                    {block.type === 'text' && ( <div className="prose prose-slate max-w-none whitespace-pre-wrap text-slate-700 leading-relaxed">{block.content}</div> )}
                    {block.type === 'image' && block.content && ( <img src={block.content} alt="Lesson" className="w-full max-h-[500px] object-contain rounded-xl border border-slate-100" /> )}
                    
                    {/* 3. HIỂN THỊ VIDEO YOUTUBE VÀ UPLOAD */}
                    {block.type === 'video' && (
                      <div className="space-y-6">
                        
                        {/* Biến chứa logic kiểm tra dữ liệu cũ/mới để tương thích */}
                        {(() => {
                          const ytUrl = block.youtubeUrl || (block.videoType === 'link' ? block.url : null);
                          const upUrl = block.uploadUrl || (block.videoType === 'upload' ? block.url : null);
                          
                          const hasYoutube = ytUrl && getYouTubeVideoId(ytUrl);
                          const hasUpload = upUrl;

                          // Nếu không có cả 2
                          if (!hasYoutube && !hasUpload) {
                              return <div className="text-slate-400 italic text-center p-6 border-dashed border rounded-xl">Chưa cấu hình link video</div>;
                          }

                          return (
                            <>
                              {/* YOUTUBE sẽ Nằm trên) */}
                              {hasYoutube && (
                                <div className="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                                  <div className="flex justify-between items-center mb-4">
                                    <h3 className="font-bold text-slate-800 text-lg flex items-center gap-2">
                                      <span className="text-red-500 text-xl">▶</span> 
                                      {block.youtubeTitle || (block.videoType === 'link' ? block.title : 'Video Bài Giảng (YouTube)')}
                                    </h3>
                                    <span className="text-xs font-bold text-slate-500 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm flex items-center gap-1">
                                      ⏱ {block.youtubeDuration || (block.videoType === 'link' ? block.duration : 0)} phút
                                    </span>
                                  </div>
                                  <div className="bg-black rounded-xl overflow-hidden aspect-video shadow-lg relative ring-1 ring-slate-900/10">
                                    <iframe className="absolute top-0 left-0 w-full h-full" src={`https://www.youtube.com/embed/${getYouTubeVideoId(ytUrl)}`} allowFullScreen></iframe>
                                  </div>
                                </div>
                              )}

                              {/* HIỂN THỊ VIDEO UPLOAD (Nằm dưới) */}
                              {hasUpload && (
                                <div className="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                                  <div className="flex justify-between items-center mb-4">
                                    <h3 className="font-bold text-slate-800 text-lg flex items-center gap-2">
                                      <span className="text-blue-500 text-xl">📁</span> 
                                      {block.uploadTitle || (block.videoType === 'upload' ? block.title : 'Tài Liệu Video (Tải Lên)')}
                                    </h3>
                                    <span className="text-xs font-bold text-slate-500 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm flex items-center gap-1">
                                      ⏱ {block.uploadDuration || (block.videoType === 'upload' ? block.duration : 0)} phút
                                    </span>
                                  </div>
                                  <div className="bg-black rounded-xl overflow-hidden shadow-lg ring-1 ring-slate-900/10">
                                    <video src={upUrl} controls className="w-full object-contain aspect-video bg-black" />
                                  </div>
                                </div>
                              )}
                            </>
                          );
                        })()}
                      </div>
                    )}

                    {block.type === 'quiz' && block.questions && (
                       // Code Render Quiz gốc của bạn...
                       <div>
                         <h4 className="font-bold text-lg text-slate-800 mb-6 flex items-center gap-2 border-b pb-4"><span className="text-2xl">📝</span> Kiểm tra kiến thức</h4>
                         <div className="space-y-6">
                           {block.questions.map((q, qIdx) => {
                             const isSubmitted = quizSubmitted[block.id];
                             const selectedAnswer = quizAnswers[block.id]?.[qIdx];
                             return (
                               <div key={qIdx} className="bg-slate-50 p-6 rounded-xl border border-slate-200">
                                 <p className="font-bold text-slate-800 mb-4"><span className="text-blue-600 mr-1">Câu {qIdx + 1}:</span> {q.question}</p>
                                 <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                   {q.options.map((opt, oIdx) => {
                                     let btnClass = "bg-white border-slate-200 text-slate-600 hover:border-blue-300 hover:bg-blue-50";
                                     if (isSubmitted) {
                                       if (oIdx === q.correctAnswerIndex) btnClass = "bg-green-100 border-green-400 text-green-800 font-bold";
                                       else if (selectedAnswer === oIdx) btnClass = "bg-red-50 border-red-400 text-red-600 line-through";
                                       else btnClass = "bg-white border-slate-200 text-slate-400 opacity-50 cursor-not-allowed";
                                     } else if (selectedAnswer === oIdx) {
                                       btnClass = "bg-blue-50 border-blue-400 text-blue-700 font-bold shadow-sm ring-2 ring-blue-100";
                                     }
                                     return (
                                       <button key={oIdx} onClick={() => handleSelectAnswer(block.id, qIdx, oIdx)} disabled={isSubmitted} className={`p-3 text-left rounded-lg border-2 transition-all duration-200 cursor-pointer ${btnClass}`}>
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
                         {!quizSubmitted[block.id] && (
                           <button onClick={() => handleSubmitQuiz(block.id)} className="mt-6 px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md transition-all active:scale-95 cursor-pointer">Kiểm tra đáp án</button>
                         )}
                       </div>
                    )}
                  </div>
                ))}
              </>
            )}
          </div>
        </div>

        {/* ================= FOOTER BÀI TRƯỚC / TIẾP THEO ================= */}
        <div className="mt-auto bg-white border-t border-slate-200 flex justify-between items-center px-10 py-6 sticky bottom-0 z-30 shadow-[0_-10px_30px_rgba(0,0,0,0.02)]">
          <button onClick={handlePrevLesson} disabled={currentLessonIndex <= 0} className={`px-6 py-2.5 font-bold transition-all rounded-xl border border-transparent ${currentLessonIndex <= 0 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-100 cursor-pointer'}`}>
            ← Bài trước
          </button>
          
          {accessMode === 'full' ? (
            !completedLessons.includes(activeItem?.id) ? (
              <button onClick={handleMarkAsComplete} disabled={activeItem?.is_locked} className="px-8 py-3 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 shadow-lg shadow-green-100 transition-all active:scale-95 cursor-pointer disabled:bg-slate-300 disabled:shadow-none">
                Hoàn thành bài học ✔
              </button>
            ) : (
              <div className="flex items-center gap-4 text-green-600 font-bold bg-green-50 px-4 py-2 rounded-xl border border-green-100">
                <span>✔ Đã học xong</span>
                <button onClick={handleNextLesson} disabled={currentLessonIndex >= allLessons.length - 1} className={`px-6 py-2 font-bold rounded-lg ml-2 transition-all ${currentLessonIndex >= allLessons.length - 1 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700 active:scale-95 cursor-pointer shadow-md'}`}>
                  Bài tiếp →
                </button>
              </div>
            )
          ) : (
            <button onClick={handleEnrollClick} className="text-orange-600 font-bold bg-orange-50 hover:bg-orange-100 px-6 py-3 rounded-xl border border-orange-200 shadow-sm transition-colors cursor-pointer">
              Đăng ký để lưu tiến độ học tập
            </button>
          )}
        </div>
      </div>
    </div>
  );
}