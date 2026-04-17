import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';

export default function CourseDetail() {
  const { courseId } = useParams();
  const navigate = useNavigate();

  const [isLoading, setIsLoading] = useState(false);
  const [newComment, setNewComment] = useState("");
  
  const [isEnrolled, setIsEnrolled] = useState(false); 

  const [course, setCourse] = useState({
    title: "Khóa học Lập trình ReactJS Thực chiến từ A-Z",
    description: "Khóa học cung cấp kiến thức nền tảng vững chắc về React, Hooks, Redux và cách xây dựng một ứng dụng hoàn chỉnh với Tailwind CSS.",
    thumbnail: "https://via.placeholder.com/800x450",
    instructor: "StudyHub Instructor",
    price: 500000,
    student_count: 1250,
    rating_score: 4.8,
    rating_count: 320,
    comments: [
      { id: 1, user: "Nguyễn Văn A", avatar: "https://via.placeholder.com/40", content: "Khóa học rất hay và chi tiết. Giảng viên hỗ trợ nhiệt tình!", date: "2 ngày trước", rating: 5 },
      { id: 2, user: "Trần Thị B", avatar: "https://via.placeholder.com/40", content: "Nội dung bám sát thực tế, tuy nhiên phần Redux hơi nhanh so với người mới.", date: "1 tuần trước", rating: 4 }
    ]
  });

  const handleSubmitComment = (e) => {
    e.preventDefault();
    if (!newComment.trim()) return;

    // Giả lập thêm bình luận mới vào danh sách (Thực tế sẽ gọi API POST lên Backend)
    const commentObj = {
      id: Date.now(),
      user: "Học viên ẩn danh", // Lấy từ user đăng nhập
      avatar: "https://via.placeholder.com/40",
      content: newComment,
      date: "Vừa xong",
      rating: 5
    };

    setCourse(prev => ({
      ...prev,
      comments: [commentObj, ...prev.comments]
    }));
    setNewComment("");
  };

  return (
    <div className="bg-slate-50 min-h-screen pb-20">
      
      {/* KHU VỰC HEADER KẾT HỢP HERO SECTION */}
      <div className="bg-slate-900 text-white py-16">
        <div className="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-12 items-center">
          
          {/* Cột trái: Thông tin khóa học */}
          <div className="lg:col-span-2 space-y-6">
            <div className="flex gap-2 text-sm font-bold text-indigo-400 uppercase tracking-wider">
              <span>Lập trình Web</span> • <span>Frontend</span>
            </div>
            <h1 className="text-4xl md:text-5xl font-black leading-tight">{course.title}</h1>
            <p className="text-slate-300 text-lg leading-relaxed max-w-3xl">
              {course.description}
            </p>
            
            {/* Chỉ số đánh giá */}
            <div className="flex flex-wrap items-center gap-6 text-sm">
              <div className="flex items-center gap-2">
                <span className="text-yellow-400 text-lg">⭐ {course.rating_score}</span>
                <span className="text-slate-400">({course.rating_count} đánh giá)</span>
              </div>
              <div className="flex items-center gap-2 text-slate-300">
                <span>👥 {course.student_count} học viên</span>
              </div>
              <div className="flex items-center gap-2 text-slate-300">
                <span>👨‍🏫 Giảng viên: <span className="text-white font-bold">{course.instructor}</span></span>
              </div>
            </div>
          </div>

          {/* Cột phải: Thumbnail & Nút mua khóa học (Sidebar dính) */}
          <div className="lg:col-span-1 relative">
            <div className="bg-white text-slate-800 rounded-2xl shadow-2xl overflow-hidden p-1 lg:absolute lg:top-[-100px] lg:w-full">
              <img src={course.thumbnail} alt={course.title} className="w-full aspect-video object-cover rounded-xl" />
              
              <div className="p-6">
                <div className="text-3xl font-black text-slate-800 mb-6">
                  {course.price === 0 ? "Miễn phí" : `${course.price.toLocaleString('vi-VN')} đ`}
                </div>
                
                {isEnrolled ? (
                  <button 
                    onClick={() => navigate(`/student/courses/${courseId}/learn`)}
                    className="w-full py-4 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-colors cursor-pointer text-lg"
                  >
                    Vào học ngay
                  </button>
                ) : (
                  <button 
                    // Chỗ này gọi hàm enrollCourse từ API của bạn
                    onClick={() => setIsEnrolled(true)} 
                    className="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors cursor-pointer text-lg"
                  >
                    Đăng ký khóa học
                  </button>
                )}
                
                <p className="text-center text-xs text-slate-400 mt-4">Cam kết hoàn tiền trong 30 ngày</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* KHU VỰC NỘI DUNG CHÍNH (Bên dưới) */}
      <div className="max-w-7xl mx-auto px-6 mt-16 grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        {/* Cột chính chiếm 2 phần */}
        <div className="lg:col-span-2 space-y-12">
          
          {/* SECTION: BÌNH LUẬN & ĐÁNH GIÁ */}
          <div className="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <h2 className="text-2xl font-black text-slate-800 mb-8 border-b pb-4">Bình luận & Đánh giá</h2>
            
            {/* 1. KHU VỰC NHẬP BÌNH LUẬN (Kiểm tra quyền) */}
            <div className="mb-10">
              {isEnrolled ? (
                // Form cho người đã mua khóa học
                <form onSubmit={handleSubmitComment} className="flex gap-4">
                  <div className="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold shrink-0">
                    You
                  </div>
                  <div className="flex-1 space-y-3">
                    <textarea 
                      rows="3"
                      placeholder="Chia sẻ cảm nghĩ của bạn về khóa học này..."
                      className="w-full border border-slate-300 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all resize-none"
                      value={newComment}
                      onChange={(e) => setNewComment(e.target.value)}
                    ></textarea>
                    <div className="flex justify-end">
                      <button 
                        type="submit"
                        disabled={!newComment.trim()}
                        className="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                      >
                        Gửi bình luận
                      </button>
                    </div>
                  </div>
                </form>
              ) : (
                // Thông báo chặn người chưa mua
                <div className="bg-slate-50 border border-slate-200 rounded-xl p-6 text-center">
                  <span className="text-4xl block mb-2">🔒</span>
                  <h4 className="font-bold text-slate-700">Bạn chưa đăng ký khóa học này</h4>
                  <p className="text-slate-500 mt-1">Chỉ những học viên đã tham gia khóa học mới có thể để lại bình luận và đánh giá.</p>
                </div>
              )}
            </div>

            {/* 2. DANH SÁCH BÌNH LUẬN (Ai cũng xem được) */}
            <div className="space-y-6">
              {course.comments.map((comment) => (
                <div key={comment.id} className="flex gap-4 p-4 hover:bg-slate-50 rounded-xl transition-colors">
                  <img src={comment.avatar} alt="avatar" className="w-12 h-12 rounded-full border border-slate-200 object-cover" />
                  <div>
                    <div className="flex items-center gap-2 mb-1">
                      <span className="font-bold text-slate-800">{comment.user}</span>
                      <span className="text-xs text-slate-400">• {comment.date}</span>
                    </div>
                    {/* Hiển thị sao tĩnh */}
                    <div className="text-yellow-400 text-xs mb-2">
                      {"⭐".repeat(comment.rating)}
                    </div>
                    <p className="text-slate-600 leading-relaxed text-sm">
                      {comment.content}
                    </p>
                  </div>
                </div>
              ))}
              
              {course.comments.length === 0 && (
                <div className="text-center text-slate-400 italic">Chưa có bình luận nào.</div>
              )}
            </div>

          </div>
        </div>
      </div>
      
    </div>
  );
}