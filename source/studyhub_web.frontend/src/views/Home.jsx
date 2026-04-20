import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';
import CourseCard from '../components/CourseCard';

export default function StudentDashboard() {
  const banners = [
    { id: 1, image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1000&auto=format&fit=crop', title: 'Học Lập Trình Từ Con Số 0', subtitle: 'Giảm giá 50% cho người mới bắt đầu' },
    { id: 2, image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1000&auto=format&fit=crop', title: 'Thành Thạo ReactJS 2024', subtitle: 'Khóa học thực chiến với dự án thực tế' },
  ];

  const navigate = useNavigate();
  const [searchTerm, setSearchTerm] = useState('');
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

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchTerm.trim()) {
      // Chuyển hướng sang trang search kèm query string
      navigate(`/student/search?q=${encodeURIComponent(searchTerm.trim())}`);
    }
  };

  return (
    <div className="w-full max-w-7xl mx-auto p-6 pb-24 space-y-12">
      {/* THANH TÌM KIẾM */}
      <form onSubmit={handleSearch} className="relative w-full max-w-3xl mx-auto">
        <div className="relative">
          <div className="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <svg className="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            className="block w-full p-4 pl-12 text-sm text-slate-900 border border-slate-200 rounded-2xl bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none shadow-sm"
            placeholder="Nhập từ khóa tìm kiếm"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <button 
            type="submit" 
            className="text-white absolute right-2.5 bottom-2.5 bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-xl text-sm px-5 py-2 transition-colors"
          >
            Tìm kiếm
          </button>
        </div>
      </form>

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
            </div>
          </div>
        ))}
      </div>

      {isLoading ? (
        <div className="py-20 text-center text-slate-500">Đang tải dữ liệu...</div>
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
              <div className="grid grid-cols-4 gap-6">
                {trendingCourses.map(course => (
                  <CourseCard key={course.courseGroupId} course={course} badge="Mới" />
                ))}
            </div>
            </section>
          )}

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