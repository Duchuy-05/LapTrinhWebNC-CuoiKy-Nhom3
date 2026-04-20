import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import CourseAPI from '../services/courseApi'; 
import Swal from 'sweetalert2';

const Courses = () => {
  const navigate = useNavigate();
  const [courses, setCourses] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchCourses();
  }, []);

  const fetchCourses = async () => {
    try {
      setIsLoading(true);
      const response = await CourseAPI.getLecturerCourses();
      const allCourses = response.data.data || [];
      
      const uniqueCoursesMap = new Map();

      // Hàm đánh trọng số ưu tiên để chọn thẻ hiển thị ra ngoài màn hình: 
      const getStatusWeight = (status) => {
        if (status === 'UNPUBLISHED') return 3;
        if (status === 'DRAFT') return 2;
        return 1; 
      };

      allCourses.forEach(course => {
        // Bản DRAFT sẽ KHÔNG bị bỏ qua và vẫn hiển thị để bạn có thể Update bình thường!
        if (course.status === 'PUBLISHED') return;

        const groupId = course.courseGroupId;

        if (!uniqueCoursesMap.has(groupId)) {
          uniqueCoursesMap.set(groupId, course);
        } else {
          // Nếu bị trùng (VD: Vừa có DRAFT vừa có UNPUBLISHED), thì ưu tiên hiện theo Trọng số
          const existingCourse = uniqueCoursesMap.get(groupId);
          if (getStatusWeight(course.status) > getStatusWeight(existingCourse.status)) {
            uniqueCoursesMap.set(groupId, course);
          }
        }
      });

      // Lấy toàn bộ values từ Map chuyển thành Array để Render
      setCourses(Array.from(uniqueCoursesMap.values()));
      
    } catch (error) {
      console.error("Lỗi:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleAddNewCourse = async () => {
    // 1. Mở Hộp thoại SweetAlert2 siêu xịn thay cho window.prompt
    const { value: title } = await Swal.fire({
      title: 'Tạo khóa học mới',
      input: 'text',
      inputLabel: 'Nhập tên khóa học mới:',
      inputPlaceholder: 'VD: Lập trình ReactJS cơ bản...',
      showCancelButton: true,
      confirmButtonText: 'Tạo ngay',
      cancelButtonText: 'Hủy bỏ',
      confirmButtonColor: '#ef4444', // Trùng màu đỏ với nút của bạn
      cancelButtonColor: '#94a3b8',
      inputValidator: (value) => {
        if (!value || value.trim() === "") {
          return 'Tên khóa học không được để trống!';
        }
      }
    });

    // 2. Nếu Giảng viên đã nhập tên và bấm Tạo ngay
    if (title) {
      try {
        // Hiện popup loading cho chuyên nghiệp (ngăn bấm nhiều lần)
        Swal.fire({
          title: 'Đang khởi tạo...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Gọi API của bạn
        const response = await CourseAPI.createDraft(title.trim());
        
        const newCourseGroupId = 
          response.data?.courseGroupId ||         
          response.data?.data?.courseGroupId ||   
          response.data?.data?._id ||             
          response.data?._id;

        if (!newCourseGroupId) {
          Swal.fire('Lỗi nghiêm trọng!', 'Backend KHÔNG trả về ID khóa học!', 'error');
          return;
        }

        // Báo thành công và tự động chuyển trang
        Swal.fire({
          title: 'Thành công!',
          text: 'Khóa học đã được tạo, đang chuyển đến trang chỉnh sửa...',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          navigate(`/lecturer/courses/${newCourseGroupId}/edit`);
        });
        
      } catch (error) {
        Swal.fire("Lỗi!", "Có lỗi xảy ra khi gọi API tạo khóa học!", "error");
        console.error("Lỗi API chi tiết:", error);
      }
    }
  };

  const handleEditCourse = (courseGroupId) => {
    navigate(`/lecturer/courses/${courseGroupId}/edit`);
  };

  // --- HÀM XUẤT BẢN ĐÃ ĐƯỢC NÂNG CẤP BẰNG SWEETALERT2 ---
  const handlePublish = async (courseGroupId) => {
    // 1. Hiển thị popup hỏi xác nhận (Confirm Dialog)
    const result = await Swal.fire({
      title: 'Xác nhận xuất bản?',
      text: "Bạn có chắc chắn muốn đưa khóa học này ra hiển thị công khai không?",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#ef4444', // Trùng màu đỏ với nút Xuất bản của bạn
      cancelButtonColor: '#94a3b8',
      confirmButtonText: 'Vâng, xuất bản ngay!',
      cancelButtonText: 'Hủy bỏ'
    });

    // 2. Nếu người dùng bấm "Vâng, xuất bản ngay!"
    if (result.isConfirmed) {
      try {
        // Bật trạng thái xoay vòng Loading...
        Swal.fire({
          title: 'Đang xử lý...',
          text: 'Vui lòng chờ trong giây lát.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Gọi API lên Server
        await CourseAPI.publishCourse(courseGroupId);
        
        // Báo thành công và chuyển trang
        Swal.fire({
          title: 'Thành công!',
          text: 'Khóa học đã chính thức LIVE!',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          navigate('/lecturer/published-courses'); 
        });

      } catch (error) {
        // Báo lỗi nếu API thất bại
        Swal.fire(
          'Lỗi xuất bản!',
          'Hệ thống không thể xuất bản lúc này, vui lòng thử lại sau.',
          'error'
        );
        console.error(error);
      }
    }
  };

  return (
    <div className="w-full">
      <div className="flex mb-8 border-b-2 border-slate-200">
        <button className="px-6 py-3 font-bold text-red-500 border-b-2 border-red-500 -mb-[2px]">
          Danh sách khóa học ({courses.length})
        </button>
      </div>

      <div className="flex items-center justify-between mb-8">
        <div className="relative w-80">
          <span className="absolute left-4 top-2.5 text-slate-400">🔍</span>
          <input type="text" placeholder="Tìm kiếm khóa học..." className="w-full py-2 pl-10 pr-4 transition-shadow border rounded-lg border-slate-300 outline-none focus:ring-2 focus:ring-blue-500/50" />
        </div>
        <button onClick={handleAddNewCourse} className="px-6 py-2.5 font-bold text-white bg-red-500 rounded-lg shadow-md hover:bg-red-600 transition-colors cursor-pointer">
          + Thêm khóa học mới
        </button>
      </div>

      {isLoading ? (
        <p className="text-slate-500">Đang tải dữ liệu...</p>
      ) : courses.length === 0 ? (
        <div className="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
          <p className="text-slate-400">Bạn chưa có bản nháp nào.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {courses.map(course => {
            // === LOGIC XỬ LÝ GIÁ THÔNG MINH ===
            const originalPrice = Number(course.price) || 0;
            
            // Kiểm tra xem backend có trả về dữ liệu giá giảm không (kể cả số 0)
            const rawDiscount = course.discountPrice;
            const hasDiscountInput = rawDiscount !== null && rawDiscount !== undefined && rawDiscount !== '';
            
            // Nếu có nhập, lấy giá giảm đó. Nếu bỏ trống thì ngầm hiểu là KHÔNG GIẢM (lấy giá gốc)
            const discountPrice = hasDiscountInput ? Number(rawDiscount) : originalPrice;
            
            // Giá cuối cùng sẽ ưu tiên giá giảm (nếu nó < giá gốc). 
            const finalPrice = discountPrice < originalPrice ? discountPrice : originalPrice;
            const isFree = finalPrice === 0;

            // YÊU CẦU: Chỉ check thêm điều kiện phải là bản UNPUBLISHED
            const isUnpublished = course.status === 'UNPUBLISHED';

            return (
              <div key={course.id || course.courseGroupId} className="flex flex-col overflow-hidden transition-all bg-white border border-gray-100 shadow-sm rounded-2xl hover:shadow-lg">
                {/* Hình ảnh */}
                <div className="relative h-44 overflow-hidden bg-slate-100 shrink-0">
                  <img src={course.image || course.thumbnail || 'https://via.placeholder.com/300x200'} alt={course.title} className="object-cover w-full h-full" />
                  
                  {/* ĐỔI MÀU BADGE: UNPUBLISHED thì hiện màu Cam cho nổi bật */}
                  <span className={`absolute top-4 -right-10 px-10 py-1.5 text-[10px] uppercase font-bold text-white rotate-45 shadow-sm ${isUnpublished ? 'bg-amber-500' : 'bg-slate-700'}`}>
                    {course.status}
                  </span>
                </div>
                
                {/* Nội dung thẻ */}
                <div className="p-5 flex flex-col flex-1">
                  
                  {/* HEADER: Tiêu đề (Trái) - Giá tiền (Phải) */}
                  <div className="flex justify-between items-start mb-3 gap-3">
                    <h3 className="text-lg font-bold text-gray-800 line-clamp-2 flex-1" title={course.title}>
                      {course.title}
                    </h3>
                    
                    {/* BẮT ĐẦU ÁP DỤNG LOGIC RENDER GIÁ */}
                    <div className="flex flex-col items-end shrink-0 text-right mt-1">
                      {isFree && isUnpublished ? (
                        // 1. CHỈ HIỂN THỊ CHỮ "MIỄN PHÍ" NẾU ĐÃ BỊ THU HỒI
                        <>
                          <span className="text-lg font-extrabold text-green-500 leading-tight uppercase">
                            Miễn phí
                          </span>
                          {originalPrice > 0 && (
                            <span className="text-xs line-through text-slate-400 mt-1 decoration-slate-400">
                              {Number(originalPrice).toLocaleString()} đ
                            </span>
                          )}
                        </>
                      ) : (
                        // 2. NẾU LÀ BẢN DRAFT, HIỆN GIÁ BÌNH THƯỜNG (Dù giá = 0 thì vẫn hiện "0 đ")
                        <>
                          <span className="text-lg font-extrabold text-blue-600 leading-tight">
                            {Number(finalPrice).toLocaleString()} đ
                          </span>
                          {/* SỬA LỖI Ở ĐÂY: Thay vì dùng discountAmount, chỉ cần so sánh originalPrice > finalPrice */}
                          {originalPrice > finalPrice && (
                            <span className="text-xs line-through text-slate-400 mt-1 decoration-slate-400">
                              {Number(originalPrice).toLocaleString()} đ
                            </span>
                          )}
                        </>
                      )}
                    </div>
                    {/* KẾT THÚC LOGIC RENDER GIÁ */}
                  </div>

                  {/* Mô tả */}
                  <p className="text-sm text-slate-500 line-clamp-2 flex-1 mb-4">
                    {course.description || "Chưa có mô tả tổng quan cho khóa học này."}
                  </p>
                  
                  {/* FOOTER: Số lượng Unit + Nút bấm */}
                  <div className="mt-auto pt-4 border-t border-slate-100">
                    
                    {/* HIỂN THỊ SỐ UNIT */}
                    <div className="w-max inline-flex items-center gap-1.5 text-[11px] font-semibold text-blue-500 mb-4 bg-blue-50 py-1.5 px-3 rounded-md">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                      </svg>
                      {course.unit_count || 0} Units
                    </div>

                    <div className="space-y-2">
                      <button 
                        onClick={() => handleEditCourse(course.courseGroupId)}
                        className="w-full py-2.5 font-bold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors cursor-pointer"
                      >
                        Chỉnh sửa
                      </button>

                      <button 
                        onClick={() => handlePublish(course.courseGroupId)}
                        className="w-full py-2.5 font-bold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors cursor-pointer shadow-sm shadow-red-200"
                      >
                        Xuất bản
                      </button>
                    </div>
                  </div>

                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};

export default Courses;