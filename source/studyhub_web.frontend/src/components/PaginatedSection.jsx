import React from 'react';
import CourseCard from './CourseCard'; // Import CourseCard cùng cấp thư mục (hoặc chỉnh lại đường dẫn cho đúng dự án của bạn)

// ── Component phân trang dùng lại ─────────────────────────────────────────────
export function Pagination({ currentPage, lastPage, onPageChange, isLoading }) {
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
export default function PaginatedSection({ title, subtitle, badge, courses, pagination, onPageChange, isLoading }) {
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