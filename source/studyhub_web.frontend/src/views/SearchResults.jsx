import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi';
import CourseCard from '../components/CourseCard';

export default function SearchResults() {
  const [searchParams, setSearchParams] = useSearchParams();
  const navigate = useNavigate();
  
  // Lấy các giá trị từ URL, nếu không có thì set giá trị mặc định
  const query = searchParams.get('q') || '';
  const initialMinPrice = searchParams.get('minPrice') || '';
  const initialMaxPrice = searchParams.get('maxPrice') || '';
  const initialSortBy = searchParams.get('sortBy') || 'newest';

  // State quản lý form bộ lọc
  const [minPrice, setMinPrice] = useState(initialMinPrice);
  const [maxPrice, setMaxPrice] = useState(initialMaxPrice);
  const [sortBy, setSortBy] = useState(initialSortBy);

  const [results, setResults] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // Gọi API mỗi khi các tham số trên URL thay đổi
  useEffect(() => {
    const fetchResults = async () => {
      try {
        setIsLoading(true);
        const response = await CourseAPI.searchCourses({
          keyword: query,
          minPrice: searchParams.get('minPrice'),
          maxPrice: searchParams.get('maxPrice'),
          sortBy: searchParams.get('sortBy') || 'newest'
        });
        setResults(response.data || []);
      } catch (error) {
        console.error("Lỗi khi tìm kiếm:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchResults();
  }, [query, searchParams]);

  // Hàm xử lý khi người dùng bấm nút "Áp dụng" bộ lọc
  const applyFilters = () => {
    const params = new URLSearchParams();
    if (query) params.set('q', query);
    if (minPrice) params.set('minPrice', minPrice);
    if (maxPrice) params.set('maxPrice', maxPrice);
    if (sortBy) params.set('sortBy', sortBy);
    
    // Cập nhật URL, useEffect sẽ tự động chạy lại để gọi API mới
    setSearchParams(params);
  };

  return (
    <div className="w-full max-w-7xl mx-auto p-6 pb-24 space-y-8">
      {/* Tiêu đề */}
      <div className="flex items-center gap-4 border-b pb-6">
        <button 
          onClick={() => navigate(-1)}
          className="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        <div>
          <h1 className="text-2xl font-bold text-slate-800">
            {query ? (
                <>Kết quả tìm kiếm cho: "<span className="text-indigo-600">{query}</span>"</>
            ) : (
                "Tất cả khóa học"
            )}
          </h1>
          <p className="mt-1 text-slate-500">Tìm thấy {results.length} khóa học phù hợp</p>
        </div>
      </div>

      {/* THANH BỘ LỌC (FILTER TOOLBAR) */}
      <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200 flex flex-wrap gap-4 items-end">
        {/* Lọc theo giá */}
        <div className="flex-1 min-w-[250px]">
            <label className="block text-sm font-medium text-slate-700 mb-1">Khoảng giá (VNĐ)</label>
            <div className="flex items-center gap-2">
                <input 
                    type="number" 
                    placeholder="Từ..." 
                    className="w-full p-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                    value={minPrice}
                    onChange={(e) => setMinPrice(e.target.value)}
                />
                <span className="text-slate-400">-</span>
                <input 
                    type="number" 
                    placeholder="Đến..." 
                    className="w-full p-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                    value={maxPrice}
                    onChange={(e) => setMaxPrice(e.target.value)}
                />
            </div>
        </div>

        {/* Sắp xếp */}
        <div className="w-full sm:w-auto min-w-[200px]">
            <label className="block text-sm font-medium text-slate-700 mb-1">Sắp xếp theo</label>
            <select 
                className="w-full p-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none bg-white"
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
            >
                <option value="newest">Mới nhất</option>
                <option value="highest_rated">Đánh giá cao nhất</option>
                <option value="popular">Nổi tiếng nhất</option>
            </select>
        </div>

        {/* Nút Áp dụng */}
        <button 
            onClick={applyFilters}
            className="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
        >
            Áp dụng bộ lọc
        </button>
      </div>

      {/* DANH SÁCH KẾT QUẢ */}
      {isLoading ? (
        <div className="py-20 text-center text-slate-500 flex flex-col items-center">
            <div className="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-4"></div>
            Đang tìm kiếm...
        </div>
      ) : results.length > 0 ? (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {results.map(course => (
            <CourseCard key={course.courseGroupId} course={course} />
          ))}
        </div>
      ) : (
        <div className="py-20 text-center">
          <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 mb-4">
             <svg className="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 className="text-xl font-semibold text-slate-700">Không tìm thấy kết quả nào</h3>
          <p className="mt-2 text-slate-500">Hãy thử nới lỏng khoảng giá hoặc chọn cách sắp xếp khác.</p>
        </div>
      )}
    </div>
  );
}