import React, { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';
import CourseCard from '../components/CourseCard';

// ── Component phân trang dùng lại ─────────────────────────────────────────────
function Pagination({ currentPage, lastPage, onPageChange, isLoading }) {
  if (lastPage <= 1) return null;

  const pages = [];
  const delta = 1; // số trang hiển thị xung quanh trang hiện tại

  for (let i = 1; i <= lastPage; i++) {
    if (
      i === 1 || i === lastPage ||
      (i >= currentPage - delta && i <= currentPage + delta)
    ) {
      pages.push(i);
    } else if (
      i === currentPage - delta - 1 ||
      i === currentPage + delta + 1
    ) {
      pages.push('...');
    }
  }

  // Loại bỏ dấu "..." trùng nhau liên tiếp
  const dedupedPages = pages.filter((p, idx) => !(p === '...' && pages[idx - 1] === '...'));

  return (
    <div className="flex items-center justify-center gap-1 mt-10">
      {/* Nút Trước */}
      <button
        onClick={() => onPageChange(currentPage - 1)}
        disabled={currentPage === 1 || isLoading}
        className="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium
                   hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600
                   disabled:opacity-40 disabled:cursor-not-allowed transition-all"
      >
        ← Trước
      </button>

      {/* Số trang */}
      {dedupedPages.map((page, idx) =>
        page === '...' ? (
          <span key={`ellipsis-${idx}`} className="px-3 py-2 text-slate-400 text-sm select-none">
            ...
          </span>
        ) : (
          <button
            key={page}
            onClick={() => onPageChange(page)}
            disabled={isLoading}
            className={`w-10 h-10 rounded-lg text-sm font-bold transition-all
              ${currentPage === page
                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 scale-105'
                : 'border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600'
              } disabled:cursor-not-allowed`}
          >
            {page}
          </button>
        )
      )}

      {/* Nút Sau */}
      <button
        onClick={() => onPageChange(currentPage + 1)}
        disabled={currentPage === lastPage || isLoading}
        className="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium
                   hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600
                   disabled:opacity-40 disabled:cursor-not-allowed transition-all"
      >
        Sau →
      </button>
    </div>
  );
}

// ── Component section có phân trang ──────────────────────────────────────────
function PaginatedSection({ title, subtitle, badge, courses, pagination, onPageChange, isLoading }) {
  return (
    <section>
      <div className="flex items-end justify-between pb-4 mb-8 border-b border-slate-200">
        <div>
          <h2 className="text-3xl font-bold text-slate-800">{title}</h2>
          {subtitle && <p className="mt-2 text-slate-500">{subtitle}</p>}
        </div>
        {pagination && (
          <span className="text-sm text-slate-400 font-medium">
            Trang {pagination.current_page}/{pagination.last_page}
            <span className="ml-2 text-slate-300">•</span>
            <span className="ml-2">{pagination.total} khóa học</span>
          </span>
        )}
      </div>

      {/* Lưới khóa học */}
      <div className={`grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 transition-opacity duration-200 ${isLoading ? 'opacity-40 pointer-events-none' : 'opacity-100'}`}>
        {courses.length === 0 && !isLoading
          ? <p className="col-span-4 text-slate-500 text-center py-10">Chưa có khóa học nào.</p>
          : courses.map(course => (
              <CourseCard key={course.courseGroupId} course={course} badge={badge} />
            ))
        }
      </div>

      {/* Phân trang */}
      {pagination && (
        <Pagination
          currentPage={pagination.current_page}
          lastPage={pagination.last_page}
          onPageChange={onPageChange}
          isLoading={isLoading}
        />
      )}
    </section>
  );
}

// ── Trang chủ chính ───────────────────────────────────────────────────────────
export default function StudentDashboard() {
  const banners = [
    { id: 1, image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1000&auto=format&fit=crop', title: 'Học Lập Trình Từ Con Số 0', subtitle: 'Giảm giá 50% cho người mới bắt đầu' },
    { id: 2, image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1000&auto=format&fit=crop', title: 'Thành Thạo ReactJS 2024', subtitle: 'Khóa học thực chiến với dự án thực tế' },
  ];

  const navigate = useNavigate();
  const [searchTerm, setSearchTerm]     = useState('');
  const [currentSlide, setCurrentSlide] = useState(0);

  // Dữ liệu các section
  const [trending, setTrending]         = useState({ data: [], current_page: 1, last_page: 1, total: 0 });
  const [mostLoved, setMostLoved]       = useState({ data: [], current_page: 1, last_page: 1, total: 0 });
  const [bestSellers, setBestSellers]   = useState([]);
  const [recommended, setRecommended]   = useState([]);

  // Loading riêng từng section
  const [initLoading, setInitLoading]         = useState(true);
  const [trendingLoading, setTrendingLoading] = useState(false);
  const [mostLovedLoading, setMostLovedLoading] = useState(false);

  // Trang hiện tại của từng section
  const [trendingPage, setTrendingPage]   = useState(1);
  const [mostLovedPage, setMostLovedPage] = useState(1);

  // Auto-slide banner
  useEffect(() => {
    const timer = setInterval(() => setCurrentSlide(prev => (prev === banners.length - 1 ? 0 : prev + 1)), 5000);
    return () => clearInterval(timer);
  }, [banners.length]);

  // Fetch tất cả dữ liệu lần đầu
  useEffect(() => {
    const fetchAll = async () => {
      try {
        setInitLoading(true);
        const res = await CourseAPI.getStudentDashboard({ trending_page: 1, most_loved_page: 1 });
        const d = res.data;
        setTrending(d.trending   || { data: [], current_page: 1, last_page: 1, total: 0 });
        setMostLoved(d.mostLoved || { data: [], current_page: 1, last_page: 1, total: 0 });
        setBestSellers(d.bestSellers || []);
        setRecommended(d.recommended || []);
      } catch (err) {
        console.error('Lỗi khi tải trang chủ:', err);
      } finally {
        setInitLoading(false);
      }
    };
    fetchAll();
  }, []);

  // Đổi trang Mới nhất
  const handleTrendingPageChange = useCallback(async (page) => {
    setTrendingPage(page);
    setTrendingLoading(true);
    try {
      const res = await CourseAPI.getStudentDashboard({ trending_page: page, most_loved_page: mostLovedPage });
      setTrending(res.data.trending || trending);
    } catch (err) {
      console.error('Lỗi phân trang Mới nhất:', err);
    } finally {
      setTrendingLoading(false);
      // Cuộn đến section
      document.getElementById('section-trending')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }, [mostLovedPage, trending]);

  // Đổi trang Yêu thích nhất
  const handleMostLovedPageChange = useCallback(async (page) => {
    setMostLovedPage(page);
    setMostLovedLoading(true);
    try {
      const res = await CourseAPI.getStudentDashboard({ trending_page: trendingPage, most_loved_page: page });
      setMostLoved(res.data.mostLoved || mostLoved);
    } catch (err) {
      console.error('Lỗi phân trang Yêu thích:', err);
    } finally {
      setMostLovedLoading(false);
      document.getElementById('section-mostloved')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }, [trendingPage, mostLoved]);

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchTerm.trim()) navigate(`/student/search?q=${encodeURIComponent(searchTerm.trim())}`);
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
          <button type="submit" className="text-white absolute right-2.5 bottom-2.5 bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-xl text-sm px-5 py-2 transition-colors">
            Tìm kiếm
          </button>
        </div>
      </form>

      {/* SLIDER QUẢNG CÁO */}
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

      {/* NỘI DUNG */}
      {initLoading ? (
        <div className="py-20 text-center text-slate-500">
          <div className="inline-block w-8 h-8 border-4 border-indigo-300 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
          <p>Đang tải dữ liệu...</p>
        </div>
      ) : (
        <>
          {/* GỢI Ý CÁ NHÂN HÓA */}
          {recommended.length > 0 && (
            <section>
              <div className="flex items-end justify-between pb-4 mb-8 border-b border-slate-200">
                <div>
                  <h2 className="text-3xl font-bold text-slate-800">Dành Riêng Cho Bạn</h2>
                  <p className="mt-2 text-slate-500">Dựa trên các kỹ năng bạn đang quan tâm.</p>
                </div>
              </div>
              <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {recommended.map(course => <CourseCard key={course.courseGroupId} course={course} badge="Gợi ý" />)}
              </div>
            </section>
          )}

          {/* KHÓA HỌC MỚI NHẤT — có phân trang */}
          <div id="section-trending">
            <PaginatedSection
              title="Khóa Học Mới Nhất"
              subtitle="Những khóa học vừa được xuất bản trên nền tảng."
              badge="Mới"
              courses={trending.data}
              pagination={trending}
              onPageChange={handleTrendingPageChange}
              isLoading={trendingLoading}
            />
          </div>

          {/* BÁN CHẠY NHẤT — không phân trang */}
          {bestSellers.length > 0 && (
            <section>
              <div className="flex items-end justify-between pb-4 mb-8 border-b border-slate-200">
                <div>
                  <h2 className="text-3xl font-bold text-slate-800">Khóa Học Bán Chạy</h2>
                  <p className="mt-2 text-slate-500">Được nhiều học viên tin tưởng lựa chọn.</p>
                </div>
              </div>
              <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {bestSellers.map(course => <CourseCard key={course.courseGroupId} course={course} badge="Hot" />)}
              </div>
            </section>
          )}

          {/* ĐƯỢC YÊU THÍCH NHẤT — có phân trang */}
          <div id="section-mostloved">
            <PaginatedSection
              title="Được Yêu Thích Nhất"
              subtitle="Xếp hạng dựa trên đánh giá thực tế từ học viên."
              badge="Yêu thích"
              courses={mostLoved.data}
              pagination={mostLoved}
              onPageChange={handleMostLovedPageChange}
              isLoading={mostLovedLoading}
            />
          </div>
        </>
      )}
    </div>
  );
}