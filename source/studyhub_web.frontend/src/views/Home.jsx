import React, { useState, useEffect } from 'react';
import CourseAPI from '../services/courseApi';

export default function StudentDashboard() {
  // Banners giữ nguyên (Có thể tạo API lấy banner sau nếu muốn)
  const banners = [
    { id: 1, image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1000&auto=format&fit=crop', title: 'Học Lập Trình Từ Con Số 0', subtitle: 'Giảm giá 50% cho người mới bắt đầu' },
    { id: 2, image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1000&auto=format&fit=crop', title: 'Thành Thạo ReactJS 2024', subtitle: 'Khóa học thực chiến với dự án thực tế' },
  ];

  const [currentSlide, setCurrentSlide] = useState(0);
  const [trendingCourses, setTrendingCourses] = useState([]);
  const [recommendedCourses, setRecommendedCourses] = useState([]);
  const [bestSellers, setBestSellers] = useState([]);
  const [mostLoved, setMostLoved] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // Auto slide
  useEffect(() => {
    const timer = setInterval(() => setCurrentSlide((prev) => (prev === banners.length - 1 ? 0 : prev + 1)), 5000);
    return () => clearInterval(timer);
  }, [banners.length]);

  // FETCH DỮ LIỆU TỪ BACKEND
  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setIsLoading(true);
        const response = await CourseAPI.getStudentDashboard();
        setTrendingCourses(response.data.trending || []);
        setRecommendedCourses(response.data.recommended || []);
        setBestSellers(response.data.bestSellers || []);
        setMostLoved(response.data.mostLoved || []);
      } catch (error) {
        console.error("Lỗi khi tải trang chủ:", error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchDashboardData();
  }, []);

  const formatPrice = (price) => {
    if (!price) return "Miễn phí"; // Fallback nếu chưa có giá
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  };

  // Component tái sử dụng cho 1 thẻ Khóa Học
  const CourseCard = ({ course, badge }) => (
    <div className="flex flex-col overflow-hidden transition-all bg-white border border-slate-200 rounded-2xl hover:shadow-xl hover:border-indigo-200 cursor-pointer group">
      <div className="relative overflow-hidden bg-slate-100 h-44">
        <img src={course.thumbnail || 'https://via.placeholder.com/500x300'} alt={course.title} className="object-cover w-full h-full transition-transform duration-500 group-hover:scale-105" />
        {badge && (
          <span className="absolute top-3 left-3 px-2.5 py-1 text-xs font-bold text-white rounded-md shadow-sm bg-yellow-500">
            {badge}
          </span>
        )}
        <div className="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 bg-black/40 group-hover:opacity-100">
          <button className="px-4 py-2 font-bold text-slate-900 transition-all transform translate-y-4 bg-white rounded-lg group-hover:translate-y-0 hover:bg-indigo-50">
            Xem chi tiết
          </button>
        </div>
      </div>
      <div className="flex flex-col flex-1 p-5">
        <h3 className="mb-2 font-bold transition-colors line-clamp-2 text-slate-800 group-hover:text-indigo-600">
          {course.title}
        </h3>
        <p className="mb-3 text-xs text-slate-500">{course.description || "Chưa có mô tả"}</p>
        <div className="flex items-end gap-2 pt-4 mt-auto border-t border-slate-100">
          <span className="text-lg font-extrabold text-slate-800">{formatPrice(course.price)}</span>
        </div>
      </div>
    </div>
  );

  return (
    <div className="w-full max-w-7xl mx-auto p-6 pb-24 space-y-12">
      
      {/* 1. SLIDER QUẢNG CÁO */}
      <div className="relative w-full h-[300px] md:h-[400px] rounded-3xl overflow-hidden shadow-2xl group">
        {banners.map((banner, index) => (
          <div key={banner.id} className={`absolute inset-0 transition-opacity duration-1000 ease-in-out ${index === currentSlide ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
            <div className="absolute inset-0 z-10 bg-gradient-to-r from-black/80 via-black/50 to-transparent"></div>
            <img src={banner.image} alt={banner.title} className="object-cover w-full h-full" />
            <div className="absolute z-20 max-w-lg text-white top-1/2 -translate-y-1/2 left-10 md:left-20">
              <span className="inline-block px-3 py-1 mb-4 text-xs font-bold tracking-wider text-indigo-900 uppercase bg-yellow-400 rounded-full">Sự kiện đặc biệt</span>
              <h2 className="mb-4 text-3xl font-extrabold leading-tight md:text-5xl">{banner.title}</h2>
              <p className="mb-8 text-lg text-slate-300 md:text-xl">{banner.subtitle}</p>
              <button className="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 transition-colors text-white font-bold rounded-xl shadow-lg">Khám phá ngay</button>
            </div>
          </div>
        ))}
      </div>

      {isLoading ? (
        <div className="py-20 text-center text-slate-500">Đang phân tích dữ liệu và đề xuất khóa học...</div>
      ) : (
        <>
          {/* 2. DẢI KHÓA HỌC: ĐỀ XUẤT */}
          {recommendedCourses.length > 0 && (
            <section>
              <div className="flex items-end justify-between pb-4 mb-8 border-b">
                <div>
                  <h2 className="text-3xl font-bold text-slate-800">Dành Riêng Cho Bạn</h2>
                  <p className="mt-2 text-slate-500">Dựa trên các kỹ năng bạn đang quan tâm.</p>
                </div>
              </div>
              <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {recommendedCourses.map(course => (
                  <CourseCard key={course.courseGroupId} course={course} badge="Gợi ý" />
                ))}
              </div>
            </section>
          )}

          {/* 3. DẢI KHÓA HỌC: THỊNH HÀNH / MỚI NHẤT*/}
          <section>
            <div className="flex items-end justify-between pb-4 mb-8 border-b">
              <div>
                <h2 className="text-3xl font-bold text-slate-800">Khóa Học Mới Nhất</h2>
                <p className="mt-2 text-slate-500">Những khóa học vừa được xuất bản trên nền tảng.</p>
              </div>
            </div>
            {trendingCourses.length === 0 ? (
              <p className="text-slate-500">Chưa có khóa học nào được xuất bản.</p>
            ) : (
              <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {trendingCourses.map(course => (
                  <CourseCard key={course.courseGroupId} course={course} badge="Mới" />
                ))}
              </div>
            )}
          </section>
          <section>
            <h2 className="text-2xl font-bold mb-6">Khóa học bán chạy nhất</h2>
            <div className="grid grid-cols-4 gap-6">
                {bestSellers.map(course => <CourseCard key={course.courseGroupId} course={course} badge="Hot" />)}
            </div>
            </section>

          <section className="mt-12">
            <h2 className="text-2xl font-bold mb-6">Được yêu thích nhất</h2>
            <div className="grid grid-cols-4 gap-6">
                {mostLoved.map(course => <CourseCard key={course.courseGroupId} course={course} badge="Yêu thích" />)}
            </div>
          </section>
        </>
      )}

    </div>
  );
}