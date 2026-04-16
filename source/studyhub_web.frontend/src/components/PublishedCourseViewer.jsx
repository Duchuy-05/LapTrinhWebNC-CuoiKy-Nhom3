import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi'; 

export default function PublishedCourseViewer() {
  const { courseId } = useParams();
  const navigate = useNavigate();

  const [courseDetails, setCourseDetails] = useState({ title: 'Đang tải...', description: '', thumbnail: null, tags: '', price: '', discountPrice: '', student_count: 0, rating_score: 0 });
  const [isRightSidebarOpen, setIsRightSidebarOpen] = useState(true);
  const [courseData, setCourseData] = useState([]);
  const [activeItem, setActiveItem] = useState(null);
  const [expandedUnits, setExpandedUnits] = useState({});
  const [blocksByLesson, setBlocksByLesson] = useState({});
  const [isUpdatingPrice, setIsUpdatingPrice] = useState(false);

  const toggleUnit = (unitId) => setExpandedUnits(prev => ({ ...prev, [unitId]: !prev[unitId] }));

  // Hàm Regex chuẩn xác 100% để lấy ID YouTube từ mọi loại Link
  const getYouTubeVideoId = (url) => {
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
  };

  useEffect(() => {
    const fetchPublishedData = async () => {
      try {
        const response = await CourseAPI.getDraft(courseId); 
        const draft = response.data.data;
        
        setCourseDetails({
          title: draft.title || '',
          description: draft.description || '',
          thumbnail: draft.thumbnail || null,
          tags: draft.tags || '',
          price: draft.price || 0,
          discountPrice: draft.discountPrice || 0,
          student_count: draft.student_count || 128, 
          rating_score: draft.rating_score || 4.8
        });

        const dbCourseData = draft.courseData || [];
        setCourseData(dbCourseData);

        const expanded = {};
        dbCourseData.forEach(u => expanded[u.id] = true);
        setExpandedUnits(expanded);

        if (dbCourseData.length > 0 && dbCourseData[0].items && dbCourseData[0].items.length > 0) {
          setActiveItem(dbCourseData[0].items[0]);
        }

        let parsedBlocks = draft.blocks || {};
        if (Array.isArray(parsedBlocks) && parsedBlocks.length > 0 && dbCourseData.length > 0) {
          parsedBlocks = { [dbCourseData[0].items[0].id]: parsedBlocks };
        }
        setBlocksByLesson(parsedBlocks);

      } catch (error) {
        console.error("Lỗi lấy dữ liệu:", error);
      }
    };

    if (courseId) fetchPublishedData();
  }, [courseId]);

const handleUpdatePrice = async () => {
    setIsUpdatingPrice(true);
    try {
      // Gọi API gửi giá trị mới xuống Backend
      await CourseAPI.updateCoursePrice(courseId, { 
        price: Number(courseDetails.price), 
        discountPrice: Number(courseDetails.discountPrice) 
      });
      
      alert("Cập nhật giá thành công! Khóa học đã áp dụng mức giá mới cho Học viên.");
    } catch (error) {
      console.error("Lỗi khi cập nhật giá:", error);
      alert("Lỗi khi cập nhật giá! Vui lòng thử lại.");
    } finally {
      setIsUpdatingPrice(false);
    }
  };
  // Hàm Ngừng xuất bản (Từ bên trong Viewer)
  const handleUnpublish = async () => {
    if (window.confirm("Bạn có chắc chắn muốn ngừng xuất bản? Học viên mới sẽ không thể tìm thấy khóa học này nữa.")) {
      try {
        await CourseAPI.unpublishCourse(courseId);
        alert("Đã ngừng xuất bản thành công!");
        navigate('/lecturer/courses'); // Đẩy về danh sách khóa học
      } catch (error) {
        alert("Lỗi khi thực hiện!");
      }
    }
  };

  const copyShareLink = () => {
    navigator.clipboard.writeText(`https://studyhub.com/course/${courseId}`);
    alert("Đã sao chép đường dẫn khóa học!");
  };

  const activeBlocks = activeItem ? (blocksByLesson[activeItem.id] || []) : [];

  return (
    <div className="flex h-[calc(100vh-64px)] bg-slate-50 border-t overflow-hidden">
      
      {/* ================= CỘT TRÁI: READ-ONLY ================= */}
      <div className="flex flex-col w-1/5 bg-white border-r shadow-sm transition-all duration-300">
        <div className="flex items-center justify-between p-4 font-bold border-b bg-slate-50 text-slate-700 text-sm">
          <span>Khung chương trình</span>
          <span className="px-2 py-1 text-[10px] font-bold text-white bg-green-500 rounded uppercase">Live</span>
        </div>
        <div className="flex-1 p-4 overflow-y-auto">
          {courseData.map(unit => (
            <div key={unit.id} className="mb-3 overflow-hidden border rounded">
              <div onClick={() => toggleUnit(unit.id)} className="flex items-center justify-between p-3 text-xs font-bold transition-colors cursor-pointer bg-slate-100 hover:bg-slate-200">
                <div className="flex items-center gap-2">
                  <span className="w-4 text-[10px] text-slate-400">{expandedUnits[unit.id] ? '▼' : '▶'}</span>
                  <span>{unit.title}</span>
                </div>
              </div>
              {expandedUnits[unit.id] && (
                <div className="bg-white">
                  {unit.items.map(item => (
                    <div 
                      key={item.id} 
                      onClick={() => setActiveItem(item)} 
                      className={`p-3 pl-8 text-xs cursor-pointer hover:bg-blue-50 transition-all border-l-4 ${activeItem?.id === item.id ? 'bg-blue-50 border-blue-600 font-bold text-blue-700' : 'border-transparent text-slate-600'}`}
                    >
                      {item.title}
                    </div>
                  ))}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* ================= CỘT GIỮA: VIEWER (KHÔNG SỬA) ================= */}
      <div className="flex-1 flex flex-col border-r shadow-inner bg-slate-100 overflow-y-auto p-8 pb-32 transition-all duration-300">
        {activeItem ? (
          <div className="max-w-4xl w-full mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            <h1 className="text-3xl font-bold text-slate-800 border-b border-slate-200 pb-6 mb-8">
              {activeItem.title}
            </h1>
            
            <div className="space-y-8">
              {activeBlocks.length === 0 && <p className="text-slate-400 italic">Bài học này chưa có nội dung.</p>}
              
              {activeBlocks.map((block, index) => (
                <div key={block.id} className="block-viewer">
                  
                  {/* TEXT VIEWER */}
                  {block.type === 'text' && (
                    <div className="prose prose-slate max-w-none whitespace-pre-wrap text-slate-700 leading-relaxed">
                      {block.content}
                    </div>
                  )}

                  {/* IMAGE VIEWER */}
                  {block.type === 'image' && block.content && (
                    <img src={block.content} alt="Lesson content" className="w-full max-h-[500px] object-contain rounded-xl border border-slate-100 shadow-sm" />
                  )}

                  {/* VIDEO VIEWER - ĐÃ FIX LỖI YOUTUBE ID */}
                  {block.type === 'video' && (
                    <div className="bg-black rounded-xl overflow-hidden aspect-video shadow-md relative">
                       {block.videoType === 'link' ? (
                          getYouTubeVideoId(block.youtubeUrl || block.url) ? (
                            <iframe
                              className="absolute top-0 left-0 w-full h-full"
                              src={`https://www.youtube.com/embed/${getYouTubeVideoId(block.youtubeUrl || block.url)}`}
                              allowFullScreen
                            ></iframe>
                          ) : (
                            <div className="flex items-center justify-center h-full w-full bg-slate-800 text-white font-medium text-sm">
                              Link YouTube không hợp lệ hoặc bị lỗi.
                            </div>
                          )
                       ) : (
                          <video src={block.uploadUrl || block.url} controls className="w-full h-full object-contain" />
                       )}
                    </div>
                  )}

                  {/* QUIZ VIEWER */}
                  {block.type === 'quiz' && block.questions && (
                    <div className="bg-slate-50 p-6 rounded-xl border border-slate-200">
                      <h4 className="font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <span>📝</span> Bài tập trắc nghiệm ({block.questions.length} câu)
                      </h4>
                      <div className="space-y-6">
                        {block.questions.map((q, qIdx) => (
                          <div key={qIdx} className="bg-white p-4 rounded-lg border border-slate-200">
                            <p className="font-bold text-slate-800 mb-3"><span className="text-blue-600">Câu {qIdx + 1}:</span> {q.question}</p>
                            <div className="grid grid-cols-2 gap-3">
                              {q.options.map((opt, oIdx) => (
                                <div key={oIdx} className={`p-2 rounded-md border ${q.correctAnswerIndex === oIdx ? 'bg-green-50 border-green-400 font-bold text-green-700' : 'bg-slate-50 border-slate-200 text-slate-600'}`}>
                                  {['A', 'B', 'C', 'D'][oIdx]}. {opt}
                                  {q.correctAnswerIndex === oIdx && <span className="ml-2">✅</span>}
                                </div>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                </div>
              ))}
            </div>
          </div>
        ) : (
          <div className="flex flex-col items-center justify-center h-full text-slate-400 font-medium space-y-2">
            <span className="text-4xl opacity-20">📖</span>
            <p>Chọn một bài học bên trái để xem nội dung</p>
          </div>
        )}
      </div>

      {/* ================= CỘT PHẢI: QUẢN LÝ LIVE & CẬP NHẬT GIÁ ================= */}
      <div className={`${isRightSidebarOpen ? 'w-1/4' : 'w-12'} transition-all duration-300 flex bg-white shadow-sm border-l overflow-hidden`}>
        <div className="w-12 border-r flex flex-col items-center py-4 bg-slate-50 shrink-0">
          <button onClick={() => setIsRightSidebarOpen(!isRightSidebarOpen)} className="w-8 h-8 flex items-center justify-center bg-white border rounded-full shadow-sm hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-90">
            {isRightSidebarOpen ? '→' : '←'}
          </button>
        </div>

        {isRightSidebarOpen && (
          <div className="flex-1 p-5 space-y-6 overflow-y-auto bg-slate-50 flex flex-col">
            
            <div>
              {/* THỐNG KÊ NHANH */}
              <div className="grid grid-cols-2 gap-3 mb-4">
                <div className="bg-white p-3 rounded-xl border border-slate-200 shadow-sm text-center">
                  <span className="block text-2xl mb-1">👨‍🎓</span>
                  <span className="block text-xl font-extrabold text-slate-800">{courseDetails.student_count}</span>
                  <span className="text-[10px] font-bold text-slate-400 uppercase">Học viên</span>
                </div>
                <div className="bg-white p-3 rounded-xl border border-slate-200 shadow-sm text-center">
                  <span className="block text-2xl mb-1">⭐</span>
                  <span className="block text-xl font-extrabold text-slate-800">{courseDetails.rating_score}</span>
                  <span className="text-[10px] font-bold text-slate-400 uppercase">Đánh giá</span>
                </div>
              </div>

              <button onClick={copyShareLink} className="w-full py-2.5 bg-white border border-blue-200 text-blue-600 font-bold rounded-xl hover:bg-blue-50 flex justify-center items-center gap-2 transition-colors cursor-pointer">
                🔗 Sao chép Link Khóa Học
              </button>

              <hr className="border-slate-200 my-6" />

              {/* QUẢN LÝ GIÁ BÁN - ĐÃ FIX MIN=0 BẰNG LOGIC */}
              <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <h3 className="font-bold text-slate-800 mb-4 flex items-center gap-2"><span>💰</span> Quản lý Giá Bán</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block mb-1 text-xs font-bold text-slate-500 uppercase">Giá gốc (VNĐ)</label>
                    <input 
                      type="number" 
                      min="0" 
                      className="w-full p-2.5 text-sm font-bold border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" 
                      value={courseDetails.price} 
                      onChange={(e) => setCourseDetails({...courseDetails, price: Math.max(0, Number(e.target.value))})} 
                    />
                  </div>
                  <div>
                    <label className="block mb-1 text-xs font-bold text-slate-500 uppercase">Giá khuyến mãi (VNĐ)</label>
                    <input 
                      type="number" 
                      min="0" 
                      className="w-full p-2.5 text-sm font-bold border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" 
                      value={courseDetails.discountPrice} 
                      onChange={(e) => setCourseDetails({...courseDetails, discountPrice: Math.max(0, Number(e.target.value))})} 
                    />
                  </div>
                  <button 
                    onClick={handleUpdatePrice} 
                    disabled={isUpdatingPrice}
                    className="w-full py-3 mt-2 font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md transition-all active:scale-95 disabled:bg-slate-400 cursor-pointer"
                  >
                    {isUpdatingPrice ? 'ĐANG CẬP NHẬT...' : 'CẬP NHẬT GIÁ MỚI'}
                  </button>
                </div>
              </div>
            </div>

            {/* NÚT NGỪNG XUẤT BẢN DƯỚI CÙNG */}
            <div className="mt-auto pt-8">
              <div className="bg-red-50 p-4 rounded-xl border border-red-100">
                <h3 className="text-xs font-bold text-red-600 mb-2 uppercase">Vùng nguy hiểm</h3>
                <p className="text-xs text-red-500/80 mb-3 leading-relaxed">
                  Ngừng xuất bản sẽ đưa khóa học này về trạng thái Nháp. Học viên mới sẽ không thể tìm hoặc mua nó nữa.
                </p>
                <button 
                  onClick={handleUnpublish}
                  className="w-full py-2.5 font-bold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-600 hover:text-white transition-colors cursor-pointer"
                >
                  Ngừng xuất bản
                </button>
              </div>
            </div>

          </div>
        )}
      </div>

    </div>
  );
}