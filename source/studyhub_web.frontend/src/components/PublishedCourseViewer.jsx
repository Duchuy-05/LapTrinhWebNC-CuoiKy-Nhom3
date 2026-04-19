import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi'; 
import CoursePricingEditor from '../components/CoursePricingEditor';

export default function PublishedCourseViewer() {
  const params = useParams();
  const navigate = useNavigate();

  const courseId = params.courseId || params.courseGroupId || params.id;
  const isLecturerMode = window.location.pathname.includes('/lecturer');

  const [courseDetails, setCourseDetails] = useState({ 
    title: 'Đang tải...', 
    price: 0, 
    discountPrice: 0, 
    final_price: 0,
    student_count: 0, 
    rating_score: 0, 
    revenue: 0 
  });
  const [courseData, setCourseData] = useState([]);
  const [activeItem, setActiveItem] = useState(null);
  const [expandedUnits, setExpandedUnits] = useState({});
  const [isUpdatingPrice, setIsUpdatingPrice] = useState(false);
  
  const [blocksByLesson, setBlocksByLesson] = useState({});

  const toggleUnit = (unitId) => setExpandedUnits(prev => ({ ...prev, [unitId]: !prev[unitId] }));

  const getYouTubeVideoId = (url) => {
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
  };

  useEffect(() => {
    const fetchPublishedData = async () => {
      if (!courseId || courseId === 'undefined') return;

      try {
        const apiCall = isLecturerMode 
          ? CourseAPI.getPublishedCourse(courseId) 
          : CourseAPI.getStudentCourseDetails(courseId);

        const response = await apiCall; 
        const publishedCourse = response.data.data;
        
        const mockStudents = publishedCourse.student_count || 128;

        const originalP = Number(publishedCourse.price) || 0;
        const rawD = publishedCourse.discountPrice;
        const hasD = rawD !== null && rawD !== undefined && rawD !== '';
        const discP = hasD ? Number(rawD) : originalP;
        const currentPrice = discP < originalP ? discP : originalP;

        setCourseDetails({
          title: publishedCourse.title || '',
          price: originalP,
          discountPrice: rawD || 0,
          final_price: currentPrice,
          student_count: publishedCourse.student_count || mockStudents, 
          rating_score: publishedCourse.rating_score || 4.8,
          revenue: publishedCourse.revenue || (mockStudents * currentPrice)
        });

        const dbCourseData = publishedCourse.courseData || [];
        setCourseData(dbCourseData);

        const expanded = {};
        dbCourseData.forEach(u => expanded[u.id] = true);
        setExpandedUnits(expanded);

        let parsedBlocks = publishedCourse.blocks || {};
        if (Array.isArray(parsedBlocks) && parsedBlocks.length > 0 && dbCourseData.length > 0) {
          parsedBlocks = { [dbCourseData[0].items[0].id]: parsedBlocks };
        }
        setBlocksByLesson(parsedBlocks);

        if (dbCourseData.length > 0 && dbCourseData[0].items && dbCourseData[0].items.length > 0) {
          setActiveItem(dbCourseData[0].items[0]);
        }

      } catch (error) {
        console.error("Lỗi API chi tiết:", error);
      }
    };

    fetchPublishedData();
  }, [courseId, isLecturerMode]);

  const handleItemClick = (item) => {
    setActiveItem(item); 
  };

  const handleUpdatePrice = async (pricingData) => {
    setIsUpdatingPrice(true);
    try {
      await CourseAPI.updateCoursePrice(courseId, { 
        price: pricingData.price, 
        discountPrice: pricingData.discountPrice 
      });
      
      const finalPrice = pricingData.discountPrice < pricingData.price ? pricingData.discountPrice : pricingData.price;
      
      setCourseDetails(prev => ({
          ...prev, 
          price: pricingData.price, 
          discountPrice: pricingData.discountPrice,
          final_price: finalPrice
      }));

      alert("Cập nhật giá thành công!");
    } catch (error) {
      alert("Lỗi khi cập nhật giá! Vui lòng thử lại.");
    } finally {
      setIsUpdatingPrice(false);
    }
  };

  // === CẬP NHẬT LOGIC NGỪNG XUẤT BẢN KHÓA HỌC ===
  const handleUnpublish = async () => {
    if (window.confirm("Bạn có chắc chắn muốn ngừng xuất bản khóa học này không? Khóa học sẽ bị ẩn khỏi học viên và chuyển về trạng thái 'Ngừng xuất bản'.")) {
      try {
        await CourseAPI.unpublishCourse(courseId);
        alert("Đã ngừng xuất bản khóa học thành công!");
        // Điều hướng giảng viên về trang danh sách (Courses.jsx)
        navigate('/lecturer/courses');
      } catch (error) {
        console.error("Lỗi khi ngừng xuất bản:", error);
        alert("Có lỗi xảy ra khi ngừng xuất bản. Vui lòng thử lại!");
      }
    }
  };

  const activeBlocks = activeItem ? (blocksByLesson[activeItem.id] || activeItem.blocks || []) : [];

  const isItemLockedVisually = (item) => {
    if (item.is_locked !== undefined) return item.is_locked;
    return courseDetails.final_price > 0 && !item.isPreview;
  };

  return (
    <div className="flex h-[calc(100vh-64px)] bg-slate-100 overflow-hidden font-sans">
      
      {/* ================= CỘT 1: KHUNG CHƯƠNG TRÌNH ================= */}
      <div className="w-[20%] bg-white border-r border-slate-200 shadow-[2px_0_10px_rgba(0,0,0,0.02)] flex flex-col z-10">
        <div className="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex justify-between items-center shadow-md">
          <div className="flex items-center gap-2.5">
            {isLecturerMode && (
              <button onClick={() => navigate('/lecturer/published-courses')} className="p-1.5 bg-white/20 hover:bg-white/30 rounded-lg transition-colors cursor-pointer active:scale-95" title="Quay lại">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M15 19l-7-7 7-7" /></svg>
              </button>
            )}
            <span className="font-bold tracking-wide text-sm">Chương Trình Học</span>
          </div>
          <span className="px-2.5 py-1 text-[9px] font-extrabold bg-white text-blue-600 rounded-full shadow-sm animate-pulse shrink-0">LIVE</span>
        </div>
        
        <div className="flex-1 p-4 overflow-y-auto space-y-3 custom-scrollbar">
          {courseData.map((unit) => (
            <div key={unit.id} className="overflow-hidden border border-slate-200 rounded-xl bg-slate-50 transition-all duration-300 hover:shadow-md">
              <div onClick={() => toggleUnit(unit.id)} className="flex items-center justify-between p-3.5 text-sm font-bold text-slate-700 cursor-pointer bg-white hover:bg-blue-50 transition-colors">
                <div className="flex items-center gap-2.5">
                  <span className={`transition-transform duration-300 text-blue-500 text-[10px] ${expandedUnits[unit.id] ? 'rotate-90' : ''}`}>▶</span>
                  <span className="line-clamp-1">{unit.title}</span>
                </div>
              </div>
              
              <div className={`transition-all duration-300 ease-in-out ${expandedUnits[unit.id] ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'}`}>
                <div className="py-2 border-t border-slate-100">
                  {unit.items.map((item, lIdx) => {
                    return (
                      <div 
                        key={item.id} 
                        onClick={() => handleItemClick(item)} 
                        className={`flex items-center justify-between p-2.5 pl-8 pr-4 text-xs font-medium cursor-pointer transition-all duration-200 ${
                          activeItem?.id === item.id 
                            ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-600' 
                            : 'text-slate-600 hover:bg-slate-200 hover:text-slate-900'
                        }`}
                      >
                        <div className="flex items-center gap-3">
                          <span className="w-5 h-5 rounded-full bg-white flex items-center justify-center text-[10px] shadow-sm font-bold shrink-0">{lIdx + 1}</span>
                          <span className="line-clamp-2 leading-relaxed opacity-90">{item.title}</span>
                        </div>
                        {isItemLockedVisually(item) ? (
                          <span className="text-slate-400 text-[10px]" title="Bài học này bị khóa với học viên">🔒</span>
                        ) : (
                        (courseDetails.final_price > 0 && item.isPreview) && (
                          <span className="text-[9px] bg-green-100 text-green-600 px-1.5 py-0.5 rounded font-bold shadow-sm">FREE</span>
                        )
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* ================= CỘT 2: VIEWER & PAYWALL ================= */}
      <div className="w-[55%] bg-slate-100 overflow-y-auto p-8 relative scroll-smooth">
        {activeItem ? (
          
          (!isLecturerMode && activeItem.is_locked) ? (
            <div className="max-w-3xl mx-auto mt-10 bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden flex flex-col items-center justify-center p-12 text-center h-[500px] relative animate-fadeIn">
              <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5"></div>
              
              <div className="relative z-10 flex flex-col items-center">
                <div className="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mb-6 shadow-inner">
                  <span className="text-5xl">🔒</span>
                </div>
                <h2 className="text-3xl font-extrabold text-slate-800 mb-4">Bạn đã hết lượt học thử!</h2>
                <p className="text-slate-500 max-w-md mx-auto mb-8 leading-relaxed">
                  Bài học <strong className="text-slate-700">"{activeItem.title}"</strong> thuộc phần nội dung nâng cao. Vui lòng đăng nhập và thanh toán để mở khóa toàn bộ khóa học.
                </p>
                <button className="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all flex items-center gap-3 cursor-pointer">
                  <span>Mở khóa toàn bộ chỉ với</span>
                  <span className="text-xl bg-white/20 px-3 py-1 rounded-lg">
                    {courseDetails.final_price?.toLocaleString()} đ
                  </span>
                </button>
              </div>
            </div>
          ) : (
            <div className="max-w-3xl mx-auto bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-fadeIn">
              <div className="bg-slate-900 p-8 text-white relative overflow-hidden">
                {activeItem.isPreview && courseDetails.price > 0 && (
                  <div className="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-4 py-1 rounded-bl-xl shadow-md z-10">
                    HỌC THỬ MIỄN PHÍ
                  </div>
                )}
                <h1 className="text-3xl font-extrabold leading-tight mb-2 relative z-10">{activeItem.title}</h1>
              </div>
              
              <div className="p-8 space-y-10">
                {activeBlocks.length === 0 ? (
                  <div className="text-center py-20 text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <span className="text-5xl block mb-3 opacity-20">📭</span><p>Nội dung bài học trống.</p>
                  </div>
                ) : (
                  activeBlocks.map((block) => (
                    <div key={block.id} className="block-viewer transform transition-all duration-500 hover:-translate-y-1">
                      {block.type === 'text' && <div className="prose prose-lg text-slate-700 leading-loose">{block.content}</div>}
                      {block.type === 'image' && block.content && (
                        <div className="rounded-2xl overflow-hidden shadow-lg border border-slate-100"><img src={block.content} alt="Lesson" className="w-full object-contain bg-slate-50 hover:scale-105 transition-transform duration-700" /></div>
                      )}
                    {block.type === 'video' && (
                      <div className="space-y-6">
                        {(() => {
                          const ytUrl = block.youtubeUrl || (block.videoType === 'link' ? block.url : null);
                          const upUrl = block.uploadUrl || (block.videoType === 'upload' ? block.url : null);
                          
                          const hasYoutube = ytUrl && getYouTubeVideoId(ytUrl);
                          const hasUpload = upUrl;

                          if (!hasYoutube && !hasUpload) {
                              return <div className="text-slate-400 italic text-center p-6 border-dashed border rounded-xl">Chưa cấu hình link video</div>;
                          }

                          return (
                            <>
                              {hasYoutube && (
                                <div className="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                                  <div className="flex justify-between items-center mb-4">
                                    <h3 className="font-bold text-slate-800 text-lg flex items-center gap-2">
                                      <span className="text-red-500 text-xl">▶</span> 
                                      {block.youtubeTitle || (block.videoType === 'link' ? block.title : 'Video Bài Giảng (YouTube)')}
                                    </h3>
                                  </div>
                                  <div className="bg-black rounded-xl overflow-hidden aspect-video shadow-lg relative ring-1 ring-slate-900/10">
                                    <iframe className="absolute top-0 left-0 w-full h-full" src={`https://www.youtube.com/embed/${getYouTubeVideoId(ytUrl)}`} allowFullScreen></iframe>
                                  </div>
                                </div>
                              )}

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
                    </div>
                  ))
                )}
              </div>
            </div>
          )

        ) : (
          <div className="flex flex-col items-center justify-center h-full text-slate-400 font-medium space-y-4">
            <div className="w-24 h-24 bg-white rounded-full shadow-lg flex items-center justify-center text-4xl animate-bounce">🎬</div>
            <p className="text-lg">Hãy chọn một bài học bên trái để xem</p>
          </div>
        )}
      </div>

      {/* ================= CỘT 3: BẢNG ĐIỀU KHIỂN & DOANH THU ================= */}
      {isLecturerMode && (
        <div className="w-[25%] bg-white border-l border-slate-200 shadow-[-5px_0_20px_rgba(0,0,0,0.03)] flex flex-col z-10 overflow-hidden relative">
          <div className="absolute top-0 right-0 w-40 h-40 bg-blue-500 rounded-full blur-3xl opacity-10 -translate-y-1/2 translate-x-1/2"></div>
          <div className="flex-1 overflow-y-auto custom-scrollbar flex flex-col p-6 relative z-10">
            <div className="mb-8">
              <span className="text-[10px] font-extrabold text-blue-600 tracking-wider uppercase mb-2 block">Tổng quan khóa học</span>
              <h2 className="text-2xl font-extrabold text-slate-800 leading-tight line-clamp-3 text-center">{courseDetails.title}</h2>
            </div>

            <div className="grid grid-cols-2 gap-4 mb-4">
              <div className="bg-gradient-to-b from-blue-50 to-white p-4 rounded-2xl border border-blue-100 shadow-sm flex flex-col items-center justify-center transform hover:-translate-y-1 transition-all">
                <span className="text-3xl mb-2">👨‍🎓</span>
                <span className="text-2xl font-black text-slate-800">{courseDetails.student_count}</span>
                <span className="text-xs font-bold text-slate-500 mt-1 text-center">Học viên theo học</span>
              </div>
              <div className="bg-gradient-to-b from-amber-50 to-white p-4 rounded-2xl border border-amber-100 shadow-sm flex flex-col items-center justify-center transform hover:-translate-y-1 transition-all">
                <span className="text-3xl mb-2">⭐</span>
                <span className="text-2xl font-black text-slate-800">{courseDetails.rating_score}</span>
                <span className="text-xs font-bold text-slate-500 mt-1 text-center">Tỉ lệ đánh giá</span>
              </div>
            </div>

            <div className="bg-gradient-to-br from-green-500 to-emerald-700 p-5 rounded-2xl shadow-lg text-white mb-8 transform hover:-translate-y-1 transition-all">
              <span className="text-xs font-bold text-green-100 uppercase tracking-widest block mb-1">Số lúa kiếm được</span>
              <div className="text-3xl font-black tracking-tight">{courseDetails.revenue.toLocaleString()} <span className="text-xl">đ</span></div>
            </div>

            <hr className="border-slate-100 mb-6" />

            <CoursePricingEditor 
              initialPrice={courseDetails.price}
              initialDiscountPrice={courseDetails.discountPrice}
              isPublished={true} 
              isSaving={isUpdatingPrice}
              onSave={handleUpdatePrice}
            />

            <div className="mt-auto pt-8">
              <button onClick={handleUnpublish} className="w-full py-3.5 text-xs font-bold text-red-600 bg-red-50 border border-red-200 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm flex items-center justify-center gap-2 group cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 group-hover:animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                NGỪNG XUẤT BẢN KHÓA HỌC
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}