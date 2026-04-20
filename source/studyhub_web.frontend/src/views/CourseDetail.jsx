import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';

export default function CourseDetail() {
  const { courseId } = useParams();
  const navigate = useNavigate();
  
  const [course, setCourse] = useState(null);
  const [isEnrolled, setIsEnrolled] = useState(false);
  const [isFree, setIsFree] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  
  const [newComment, setNewComment] = useState("");
  const [rating, setRating] = useState(5);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Lấy thông tin chi tiết khóa học từ Public API
  const fetchDetail = async () => {
    try {
      setIsLoading(true);
      const response = await CourseAPI.getCourseDetail(courseId);
      setCourse(response.data.data);
      setIsEnrolled(response.data.isEnrolled);
      setIsFree(response.data.isFree);
    } catch (err) {
      console.error("Lỗi khi tải chi tiết khóa học:", err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchDetail();
  }, [courseId]);

  // Xử lý nút Học miễn phí / Vào học
  const handleEnrollOrLearn = async () => {
    const token = localStorage.getItem('token');
    
    if (!token) {
      if (window.confirm("Vui lòng đăng nhập để bắt đầu học và lưu lại tiến độ của bạn!")) {
        navigate('/login');
      }
      return;
    }

    if (isFree && !isEnrolled) {
      try {
        await CourseAPI.enrollCourse(courseId);
        navigate(`/student/courses/${courseId}/learn`);
      } catch (e) {
        alert(e.response?.data?.message || "Không thể đăng ký khóa học.");
      }
      return;
    }

    navigate(`/student/courses/${courseId}/learn`);
  };

  // Xử lý gửi bình luận đánh giá
  const handleSubmitComment = async (e) => {
    e.preventDefault();
    if (!newComment.trim() || isSubmitting) return;

    try {
      setIsSubmitting(true);
      await CourseAPI.submitCourseComment(courseId, { // Đã sửa theo bước trước
        content: newComment,
        rating: rating
      });
      
      setNewComment("");
      setRating(5);
      alert("Cảm ơn bạn đã để lại đánh giá!");
      fetchDetail(); 
    } catch (err) {
      alert(err.response?.data?.message || "Bạn cần tham gia khóa học để để lại đánh giá.");
    } finally {
      setIsSubmitting(false);
    }
  };

  // --- TÍNH TOÁN SỐ LƯỢNG BÀI HỌC ---
  const totalUnits = course?.courseData?.length || 0;
  const totalLessons = course?.courseData?.reduce((acc, unit) => acc + (unit.items?.length || 0), 0) || 0;

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-slate-50">
        <div className="animate-pulse flex flex-col items-center">
          <div className="w-12 h-12 bg-indigo-200 rounded-full mb-4"></div>
          <p className="text-slate-400 font-medium">Đang tải thông tin khóa học...</p>
        </div>
      </div>
    );
  }

  if (!course) {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen text-slate-500">
        <span className="text-6xl mb-4">🔍</span>
        <p className="text-xl font-bold">Không tìm thấy khóa học</p>
        <button onClick={() => navigate('/student/home')} className="mt-4 text-indigo-600 font-bold hover:underline">Quay lại trang chủ</button>
      </div>
    );
  }

  return (
    <div className="bg-slate-50 min-h-screen pb-20">
      {/* SECTION: HERO / HEADER */}
      <div className="bg-slate-900 text-white py-16 md:py-24">
        <div className="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
          
          <div className="lg:col-span-2 space-y-6">
            <h1 className="text-4xl md:text-5xl font-black leading-tight">{course.title}</h1>
            <p className="text-slate-300 text-lg md:text-xl leading-relaxed max-w-3xl">
              {course.description}
            </p>
            
            <div className="flex flex-wrap items-center gap-6 text-sm">
              <div className="flex items-center gap-2">
                <span className="text-yellow-400 text-xl font-bold">★ {course.rating_score || 0}</span>
                <span className="text-slate-400">({course.rating_count || 0} đánh giá)</span>
              </div>
              <div className="flex items-center gap-2 text-slate-300 border-l border-slate-700 pl-6">
                <span className="font-bold">{course.student_count || 0}</span> học viên
              </div>
              <div className="flex items-center gap-2 text-slate-300 border-l border-slate-700 pl-6">
                Giảng viên: <span className="text-white font-bold">{course.author_name}</span>
              </div>
            </div>
          </div>

          {/* SIDEBAR: CARD ĐĂNG KÝ */}
          <div className="lg:col-span-1 lg:sticky lg:top-8 z-20">
            <div className="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
              <div className="relative group">
                <img src={course.thumbnail} alt={course.title} className="w-full aspect-video object-cover" />
                <div className="absolute inset-0 bg-slate-900/20 group-hover:bg-slate-900/0 transition-all flex items-center justify-center">
                   <button onClick={() => navigate(`/student/courses/${courseId}/learn`)} className="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center text-indigo-600 text-2xl shadow-xl hover:scale-110 transition-transform cursor-pointer">▶</button>
                </div>
              </div>
              
              <div className="p-8 text-slate-800">
                <div className="flex items-baseline gap-3 mb-6">
                  {isFree ? (
                    <span className="text-3xl font-black text-green-600 uppercase">Miễn phí</span>
                  ) : (
                    <>
                      <span className="text-4xl font-black text-slate-900">
                        {(course.discountPrice !== null && course.discountPrice !== undefined
                          ? Number(course.discountPrice)
                          : Number(course.price)
                        ).toLocaleString('vi-VN')}đ
                      </span>
                      {course.discountPrice !== null && course.discountPrice !== undefined && (
                        <span className="text-lg text-slate-400 line-through">
                          {Number(course.price).toLocaleString('vi-VN')}đ
                        </span>
                      )}
                    </>
                  )}
                </div>

                <div className="flex flex-col gap-3">
                  {isEnrolled ? (
                    <button 
                      onClick={() => navigate(`/student/courses/${courseId}/learn`)}
                      className="w-full py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition-all shadow-lg shadow-green-100 cursor-pointer"
                    >
                      VÀO HỌC NGAY
                    </button>
                  ) : (
                    <>
                      {isFree ? (
                        <button 
                          onClick={handleEnrollOrLearn}
                          className="w-full py-4 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition-all shadow-lg shadow-green-100 cursor-pointer"
                        >
                          BẮT ĐẦU HỌC MIỄN PHÍ
                        </button>
                      ) : (
                        <>
                          <button 
                            onClick={() => navigate(`/student/courses/${courseId}/learn`)}
                            className="w-full py-4 bg-orange-50 text-orange-600 border-2 border-orange-100 font-bold rounded-2xl hover:bg-orange-100 transition-all cursor-pointer"
                          >
                            HỌC THỬ (PREVIEW)
                          </button>
                          <button 
                            onClick={() => navigate('/login')}
                            className="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 cursor-pointer"
                          >
                            ĐĂNG KÝ MUA NGAY
                          </button>
                        </>
                      )}
                    </>
                  )}
                </div>
                
                <div className="mt-8 space-y-3">
                  <p className="text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Khóa học này bao gồm</p>
                  <ul className="text-sm text-slate-600 space-y-2">
                    <li className="flex items-center gap-2">✅ Truy cập trọn đời nội dung</li>
                    <li className="flex items-center gap-2">✅ Tài liệu bài giảng đính kèm</li>
                    <li className="flex items-center gap-2">✅ Chứng chỉ hoàn thành</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div className="max-w-7xl mx-auto px-6 mt-16 grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div className="lg:col-span-2 space-y-12">
          
          {/* ========================================= */}
          {/* MỚI THÊM: SECTION NỘI DUNG KHÓA HỌC      */}
          {/* ========================================= */}
          <div className="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 md:p-12">
            <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8 border-b pb-4 gap-4">
              <h2 className="text-2xl font-black text-slate-800">Nội dung khóa học</h2>
              <div className="text-sm font-medium text-slate-500">
                <span className="font-bold text-slate-700">{totalUnits}</span> chương • <span className="font-bold text-slate-700">{totalLessons}</span> bài học
              </div>
            </div>

            <div className="space-y-4">
              {course.courseData && course.courseData.length > 0 ? (
                course.courseData.map((unit, unitIndex) => (
                  <div key={unit.id || unitIndex} className="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                    {/* Header của Chương */}
                    <div className="bg-slate-100 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                      <h3 className="font-bold text-slate-800">
                        {unitIndex + 1}. {unit.title || "Chương mới"}
                      </h3>
                      <span className="text-xs font-semibold text-slate-500 bg-white px-2 py-1 rounded shadow-sm border border-slate-200">
                        {unit.items?.length || 0} bài học
                      </span>
                    </div>
                    
                    {/* Danh sách các bài học bên trong Chương */}
                    <div className="divide-y divide-slate-100">
                      {unit.items && unit.items.map((lesson, lessonIndex) => (
                        <div key={lesson.id || lessonIndex} className="px-6 py-4 flex items-center justify-between hover:bg-indigo-50/50 transition-colors group">
                          <div className="flex items-center gap-4">
                            <div className="w-8 h-8 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 group-hover:text-indigo-600 group-hover:border-indigo-200 transition-colors shrink-0">
                              ▶
                            </div>
                            <span className="text-slate-700 font-medium group-hover:text-indigo-700">
                              {lesson.title || "Bài học mới"}
                            </span>
                          </div>
                          
                          {/* Hiển thị thẻ "Học thử" nếu bài học được phép preview và user chưa mua */}
                          {!isEnrolled && !isFree && lesson.isPreview && (
                            <span className="text-[10px] font-bold text-orange-600 bg-orange-100 px-2 py-1 rounded-full uppercase tracking-widest ml-4 shrink-0 border border-orange-200">
                              Học thử
                            </span>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                  <p className="text-slate-500 italic">Khóa học này đang được giảng viên cập nhật nội dung.</p>
                </div>
              )}
            </div>
          </div>
          {/* ========================================= */}


          {/* SECTION: BÌNH LUẬN & ĐÁNH GIÁ (Giữ nguyên như cũ) */}
          <div className="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 md:p-12">
            <h2 className="text-2xl font-black text-slate-800 mb-10 border-b pb-6">Đánh giá từ cộng đồng</h2>

            {/* FORM GỬI BÌNH LUẬN */}
            <div className="mb-12">
              {isEnrolled ? (
                <form onSubmit={handleSubmitComment} className="space-y-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                  <div className="flex items-center gap-4">
                    <span className="font-bold text-slate-700">Đánh giá của bạn:</span>
                    <div className="flex gap-1">
                      {[1, 2, 3, 4, 5].map((star) => (
                        <button 
                          key={star} 
                          type="button" 
                          onClick={() => setRating(star)}
                          className={`text-2xl transition-transform active:scale-125 cursor-pointer ${rating >= star ? 'text-yellow-400' : 'text-slate-300'}`}
                        >
                          ★
                        </button>
                      ))}
                    </div>
                  </div>
                  <textarea 
                    rows="4"
                    className="w-full border-2 border-slate-200 rounded-xl p-4 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all resize-none bg-white text-slate-700"
                    placeholder="Chia sẻ trải nghiệm thực tế của bạn về khóa học này nhé..."
                    value={newComment}
                    onChange={(e) => setNewComment(e.target.value)}
                  ></textarea>
                  <div className="flex justify-end">
                    <button 
                      type="submit"
                      disabled={!newComment.trim() || isSubmitting}
                      className="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition-all shadow-md shadow-indigo-100 cursor-pointer"
                    >
                      {isSubmitting ? "Đang gửi..." : "Gửi đánh giá"}
                    </button>
                  </div>
                </form>
              ) : (
                <div className="bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-10 text-center">
                  <div className="text-4xl mb-3">🔒</div>
                  <h4 className="font-bold text-slate-700">Hãy tham gia khóa học để bình luận</h4>
                  <p className="text-slate-500 text-sm mt-1">Góp ý của bạn giúp cộng đồng học tập phát triển tốt hơn.</p>
                </div>
              )}
            </div>

            {/* DANH SÁCH BÌNH LUẬN */}
            <div className="space-y-8">
              {(course.comments || []).length > 0 ? (
                course.comments.map((comment, index) => (
                  <div key={comment.id || index} className="flex gap-5 group">
                    <img 
                      src={comment.avatar || `https://ui-avatars.com/api/?name=${comment.user_name}&background=random`} 
                      alt="avatar" 
                      className="w-12 h-12 rounded-full border-2 border-white shadow-sm object-cover shrink-0" 
                    />
                    <div className="flex-1 border-b border-slate-100 pb-8 group-last:border-0">
                      <div className="flex items-center justify-between mb-2">
                        <span className="font-bold text-slate-800">{comment.user_name}</span>
                        <span className="text-xs text-slate-400">{comment.created_at}</span>
                      </div>
                      <div className="text-yellow-400 text-xs mb-3">
                        {"★".repeat(comment.rating)}{"☆".repeat(5 - comment.rating)}
                      </div>
                      <p className="text-slate-600 leading-relaxed italic">
                        "{comment.content}"
                      </p>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-10">
                  <p className="text-slate-400 italic">Khóa học này chưa có đánh giá nào.</p>
                </div>
              )}
            </div>

          </div>
        </div>
      </div>
    </div>
  );
}