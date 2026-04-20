import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import BlockEditor from '../components/BlockEditor'; 
import CourseAPI from '../services/courseApi'; 
import Swal from 'sweetalert2'; 

export default function CourseEditor() {
  const { courseId } = useParams();
  const navigate = useNavigate();

  const [courseDetails, setCourseDetails] = useState({ title: 'Đang tải...', description: '', thumbnail: null, tags: '', price: '' });
  const [isRightSidebarOpen, setIsRightSidebarOpen] = useState(true);
  const [courseData, setCourseData] = useState([]);
  const [activeItem, setActiveItem] = useState(null);
  const [expandedUnits, setExpandedUnits] = useState({});
  const [blocksByLesson, setBlocksByLesson] = useState({});
  const [isUploadingThumb, setIsUploadingThumb] = useState(false);
  
  const [openUnitMenu, setOpenUnitMenu] = useState(null);

  const toggleUnit = (unitId) => setExpandedUnits(prev => ({ ...prev, [unitId]: !prev[unitId] }));
  
  const isAllLessonsFree = React.useMemo(() => {
    let totalLessons = 0;
    let freeLessons = 0;
    courseData.forEach(unit => {
      (unit.items || []).forEach(item => {
        totalLessons++;
        if (item.isPreview) freeLessons++;
      });
    });
    return totalLessons > 0 && totalLessons === freeLessons;
  }, [courseData]);

  useEffect(() => {
    if (isAllLessonsFree) {
      setCourseDetails(prev => ({ ...prev, price: '', discountPrice: '' }));
    }
  }, [isAllLessonsFree]);

  // --- 1. THÊM UNIT (Đã dùng SweetAlert2) ---
  const handleAddUnit = async () => {
    const { value: title } = await Swal.fire({
      title: 'Thêm Unit mới',
      input: 'text',
      inputLabel: 'Nhập tên Unit:',
      inputPlaceholder: 'VD: Chương 1: Giới thiệu khóa học',
      showCancelButton: true,
      confirmButtonText: 'Thêm',
      cancelButtonText: 'Hủy',
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#94a3b8',
      inputValidator: (value) => {
        if (!value || value.trim() === "") {
          return 'Tên Unit không được để trống!';
        }
      }
    });

    if (title?.trim()) {
      const newId = `u${Date.now()}`;
      setCourseData([...courseData, { id: newId, title: title.trim(), items: [] }]);
      setExpandedUnits(prev => ({ ...prev, [newId]: true }));
      // Thông báo nhẹ góc màn hình
      Swal.fire({ title: 'Đã thêm Unit!', icon: 'success', timer: 1000, showConfirmButton: false, position: 'top-end', toast: true });
    }
  };

  // --- 2. THÊM BÀI HỌC (Đã dùng SweetAlert2) ---
  const handleAddLesson = async (unitId) => {
    const { value: title } = await Swal.fire({
      title: 'Thêm bài học mới',
      input: 'text',
      inputLabel: 'Nhập tên bài học:',
      inputPlaceholder: 'VD: Bài 1: Cài đặt môi trường',
      showCancelButton: true,
      confirmButtonText: 'Thêm',
      cancelButtonText: 'Hủy',
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#94a3b8',
      inputValidator: (value) => {
        if (!value || value.trim() === "") {
          return 'Tên bài học không được để trống!';
        }
      }
    });

    if (title?.trim()) {
      const newLessonId = `l${Date.now()}`;
      setCourseData(courseData.map(u => u.id === unitId ? { ...u, items: [...u.items, { id: newLessonId, title: title.trim(), isPreview: false }] } : u));
      setBlocksByLesson(prev => ({ ...prev, [newLessonId]: [] }));
      Swal.fire({ title: 'Đã thêm bài học!', icon: 'success', timer: 1000, showConfirmButton: false, position: 'top-end', toast: true });
    }
  };

  // --- 3. ĐỔI TÊN UNIT (Đã dùng SweetAlert2) ---
  const handleEditUnit = async (e, unitId, currentTitle) => {
    e.stopPropagation(); 
    setOpenUnitMenu(null); 
    
    const { value: newTitle } = await Swal.fire({
      title: 'Đổi tên Unit',
      input: 'text',
      inputValue: currentTitle,
      showCancelButton: true,
      confirmButtonText: 'Lưu thay đổi',
      cancelButtonText: 'Hủy',
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#94a3b8',
      inputValidator: (value) => {
        if (!value || value.trim() === "") {
          return 'Tên Unit không được để trống!';
        }
      }
    });

    if (newTitle && newTitle.trim()) {
      setCourseData(courseData.map(u => u.id === unitId ? { ...u, title: newTitle.trim() } : u));
      Swal.fire({ title: 'Đã cập nhật tên!', icon: 'success', timer: 1000, showConfirmButton: false, position: 'top-end', toast: true });
    }
  };

  // --- 4. XÓA UNIT (Đã dùng SweetAlert2) ---
  const handleDeleteUnit = async (e, unitId) => {
    e.stopPropagation(); 
    setOpenUnitMenu(null); 

    const result = await Swal.fire({
      title: 'Xóa Unit này?',
      text: "Toàn bộ bài học bên trong cũng sẽ bị xóa vĩnh viễn!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#94a3b8',
      confirmButtonText: 'Vâng, xóa nó!',
      cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
      setCourseData(courseData.filter(u => u.id !== unitId));
      if (activeItem && !courseData.filter(u => u.id !== unitId).some(u => u.items.some(i => i.id === activeItem.id))) {
        setActiveItem(null);
      }
      Swal.fire({ title: 'Đã xóa!', icon: 'success', timer: 1000, showConfirmButton: false });
    }
  };

  const toggleMenu = (e, unitId) => {
    e.stopPropagation();
    setOpenUnitMenu(openUnitMenu === unitId ? null : unitId);
  };

  // --- CHUYỂN ĐỔI CHẾ ĐỘ HỌC THỬ ---
  const handleTogglePreview = () => {
    const newValue = !activeItem.isPreview;
    setActiveItem(prev => ({ ...prev, isPreview: newValue }));
    
    setCourseData(prevData => prevData.map(unit => ({
      ...unit,
      items: unit.items.map(item => 
        item.id === activeItem.id ? { ...item, isPreview: newValue } : item
      )
    })));
  };

  useEffect(() => {
    const fetchDraftData = async () => {
      try {
        const response = await CourseAPI.getDraft(courseId);
        const draft = response.data.data;
        
        setCourseDetails({
          title: draft.title || '',
          description: draft.description || '',
          thumbnail: draft.thumbnail || null,
          tags: draft.tags || '',
          price: draft.price || '',
        });

        const dbCourseData = draft.courseData || [];
        setCourseData(dbCourseData);

        const expanded = {};
        dbCourseData.forEach(u => expanded[u.id] = true);
        setExpandedUnits(expanded);

        if (dbCourseData.length > 0 && dbCourseData[0].items && dbCourseData[0].items.length > 0) {
          setActiveItem(dbCourseData[0].items[0]);
        } else {
          setActiveItem(null);
        }

        let parsedBlocks = draft.blocks || {};
        if (Array.isArray(parsedBlocks)) {
          if (parsedBlocks.length > 0 && dbCourseData.length > 0 && dbCourseData[0].items.length > 0) {
            const firstLessonId = dbCourseData[0].items[0].id;
            parsedBlocks = { [firstLessonId]: parsedBlocks };
          } else {
            parsedBlocks = {};
          }
        }
        setBlocksByLesson(parsedBlocks);

      } catch (error) {
        console.error("Lỗi lấy dữ liệu bản nháp:", error);
      }
    };

    if (courseId) fetchDraftData();
  }, [courseId]);

  // --- 5. TẢI ẢNH LÊN (Đã dùng SweetAlert2 báo lỗi) ---
  const handleThumbnailUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setIsUploadingThumb(true);
    try {
      const response = await CourseAPI.uploadImage(file);
      const imageUrl = response.data.imageUrl;
      setCourseDetails({ ...courseDetails, thumbnail: imageUrl });
    } catch (error) {
      Swal.fire("Lỗi!", "Không thể tải ảnh lên. Vui lòng kiểm tra lại!", "error");
    } finally {
      setIsUploadingThumb(false);
    }
  };

  // --- 6. LƯU THÔNG TIN (Đã dùng SweetAlert2 Loading) ---
  const handleSaveDraft = async () => {
    const currentPrice = Number(courseDetails.price) || 0;

    try {
      // Bật loading mượt mà ngăn người dùng click nhiều lần
      Swal.fire({
        title: 'Đang lưu thông tin...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
      });

      const payload = {
        title: courseDetails.title || '',
        description: courseDetails.description || '',
        thumbnail: courseDetails.thumbnail || null,
        tags: courseDetails.tags || '',
        price: currentPrice,
        courseData: courseData || [],
        blocks: blocksByLesson || {}
      };

      await CourseAPI.updateDraft(courseId, payload);
      
      Swal.fire({
        title: 'Thành công!',
        text: 'Đã lưu thông tin khóa học!',
        icon: 'success',
        confirmButtonColor: '#3b82f6',
        timer: 1500,
        showConfirmButton: false
      }).then(() => {
        navigate('/lecturer/courses');
      });

    } catch (error) {
      Swal.fire({
        title: 'Lưu thất bại!',
        text: 'Đã có lỗi xảy ra khi lưu vào máy chủ.',
        icon: 'error',
        confirmButtonColor: '#ef4444'
      });
    }
  };

  return (
    <div className="flex h-[calc(100vh-64px)] bg-slate-50 border-t overflow-hidden relative">
      
      {openUnitMenu && (
        <div className="fixed inset-0 z-10" onClick={() => setOpenUnitMenu(null)}></div>
      )}

      {/* CỘT TRÁI */}
      <div className="flex flex-col w-1/5 bg-white border-r shadow-sm transition-all duration-300 z-20">
        
        <div className="flex items-center justify-between p-4 font-bold border-b bg-slate-50 text-slate-700 text-sm">
          <div className="flex items-center gap-2">
            <button 
              onClick={() => navigate('/lecturer/courses')} 
              className="p-1 text-slate-500 hover:text-slate-800 hover:bg-slate-200 rounded transition-colors cursor-pointer active:scale-95"
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <span>Nội dung</span>
          </div>
          <button onClick={handleAddUnit} className="px-2 py-1 text-[10px] font-semibold text-blue-600 bg-blue-100 rounded hover:bg-blue-600 hover:text-white transition-all cursor-pointer">+ Unit</button>
        </div>
        
        <div className="flex-1 p-4 overflow-y-auto">
          {courseData.length === 0 && <p className="text-xs text-slate-400 text-center mt-10">Chưa có nội dung.</p>}
          {courseData.map(unit => (
            <div key={unit.id} className="mb-3 overflow-visible border rounded relative">
              
              <div onClick={() => toggleUnit(unit.id)} className="flex items-center justify-between p-3 text-xs font-medium transition-colors cursor-pointer bg-slate-100 hover:bg-slate-200 group">
                <div className="flex items-center gap-2 pr-2">
                  <span className="w-4 text-[10px] text-slate-400">{expandedUnits[unit.id] ? '▼' : '▶'}</span>
                  <span className="font-bold text-slate-700">{unit.title}</span>
                </div>

                <div className="relative">
                  <button 
                    onClick={(e) => toggleMenu(e, unit.id)}
                    className="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-300 rounded transition-all cursor-pointer"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                  </button>

                  {openUnitMenu === unit.id && (
                    <div className="absolute right-0 top-6 mt-1 w-28 bg-white border border-slate-200 shadow-xl rounded-md overflow-hidden z-50">
                      <button onClick={(e) => handleEditUnit(e, unit.id, unit.title)} className="flex items-center gap-2 w-full text-left px-3 py-2 text-[11px] font-bold text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">Đổi tên</button>
                      <button onClick={(e) => handleDeleteUnit(e, unit.id)} className="flex items-center gap-2 w-full text-left px-3 py-2 text-[11px] font-bold text-red-500 hover:bg-red-50 transition-colors">Xóa Unit</button>
                    </div>
                  )}
                </div>
              </div>

              {expandedUnits[unit.id] && (
                <div className="bg-white pb-2">
                  {unit.items.map(item => (
                    <div 
                      key={item.id} 
                      onClick={() => setActiveItem(item)} 
                      className={`flex items-center justify-between p-2.5 pl-8 pr-4 text-xs cursor-pointer hover:bg-blue-50 transition-all ${activeItem?.id === item.id ? 'bg-blue-100 border-l-4 border-blue-600 font-medium' : ''}`}
                    >
                      <span className="line-clamp-1">{item.title}</span>
                      {item.isPreview && <span className="text-[9px] bg-green-500 text-white px-1.5 py-0.5 rounded shadow-sm font-bold ml-2 shrink-0">FREE</span>}
                    </div>
                  ))}
                  <div onClick={() => handleAddLesson(unit.id)} className="p-2 pl-8 mt-1 text-[10px] font-bold text-blue-500 cursor-pointer hover:bg-slate-50">+ Thêm bài học...</div>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* CỘT GIỮA: TRÌNH SOẠN THẢO CÓ THANH CÔNG CỤ */}
      <div className="flex-1 flex flex-col border-r shadow-inner bg-white overflow-hidden transition-all duration-300 z-0">
        {activeItem ? (
          <div className="flex flex-col h-full">
            
            <div className="flex items-center justify-between px-6 py-3 bg-slate-50 border-b border-slate-200 shrink-0">
              <span className="text-sm font-bold text-slate-500">Cài đặt bài học:</span>
              <label className="flex items-center gap-3 cursor-pointer group">
                <span className={`text-sm font-bold transition-colors ${activeItem.isPreview ? 'text-green-600' : 'text-slate-400 group-hover:text-blue-600'}`}>
                  Mở khóa "Free"
                </span>
                <div className="relative">
                  <input 
                    type="checkbox" 
                    className="sr-only peer"
                    checked={activeItem.isPreview || false}
                    onChange={handleTogglePreview}
                  />
                  <div className="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                </div>
              </label>
            </div>

            <div className="flex-1 overflow-y-auto">
              <BlockEditor 
                lessonTitle={activeItem.title} 
                blocks={blocksByLesson[activeItem.id] || []} 
                setBlocks={(newBlocks) => setBlocksByLesson({...blocksByLesson, [activeItem.id]: newBlocks})} 
              />
            </div>

          </div>
        ) : (
          <div className="flex flex-col items-center justify-center h-full text-slate-400 font-medium space-y-2">
            <span className="text-4xl opacity-20">📝</span>
            <p>Chọn hoặc tạo một bài học để bắt đầu soạn thảo</p>
          </div>
        )}
      </div>

      {/* CỘT PHẢI: THIẾT LẬP KHÓA HỌC */}
      <div className={`${isRightSidebarOpen ? 'w-1/5' : 'w-12'} transition-all duration-300 flex bg-white shadow-sm border-l overflow-hidden z-0`}>
        <div className="w-12 border-r flex flex-col items-center py-4 bg-slate-50 shrink-0">
          <button onClick={() => setIsRightSidebarOpen(!isRightSidebarOpen)} className="w-8 h-8 flex items-center justify-center bg-white border rounded-full shadow-sm hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-90">
            {isRightSidebarOpen ? '→' : '←'}
          </button>
          {!isRightSidebarOpen && (
            <div className="mt-8 [writing-mode:vertical-lr] rotate-180 text-xs font-bold text-slate-400 uppercase tracking-widest">
              Thiết lập khóa học
            </div>
          )}
        </div>

        {isRightSidebarOpen && (
          <div className="flex-1 p-5 space-y-6 overflow-y-auto">
            <div>
              <label className="block mb-2 text-sm font-bold text-slate-600">Tên khóa học</label>
              <input type="text" className="w-full p-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all" value={courseDetails.title} onChange={(e) => setCourseDetails({...courseDetails, title: e.target.value})} />
            </div>
            <div>
              <label className="block mb-2 text-sm font-bold text-slate-600">Mô tả ngắn</label>
              <textarea className="w-full h-28 p-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none" placeholder="Mô tả tóm tắt về khóa học..." value={courseDetails.description} onChange={(e) => setCourseDetails({...courseDetails, description: e.target.value})}></textarea>
            </div>
            <div>
              <label className="block mb-2 text-sm font-bold text-slate-600">Ảnh đại diện</label>
              <div className="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl bg-slate-50 border-slate-300 hover:bg-blue-50 transition-all overflow-hidden group">
                {isUploadingThumb ? (
                  <span className="text-sm font-bold text-blue-500 animate-pulse">ĐANG TẢI...</span>
                ) : courseDetails.thumbnail ? (
                  <>
                    <img src={courseDetails.thumbnail} alt="Thumbnail" className="object-cover w-full h-full" />
                    <label className="absolute inset-0 flex flex-col items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                      <span className="text-white text-xs font-bold px-3 py-1.5 border border-white rounded-lg">Đổi ảnh khác</span>
                      <input type="file" className="hidden" accept="image/*" onChange={handleThumbnailUpload} />
                    </label>
                  </>
                ) : (
                  <label className="flex flex-col items-center justify-center w-full h-full cursor-pointer">
                    <span className="text-2xl mb-1">📸</span>
                    <span className="text-[10px] text-slate-500 font-medium text-center px-4 uppercase">Nhấn để tải ảnh lên</span>
                    <input type="file" className="hidden" accept="image/*" onChange={handleThumbnailUpload} />
                  </label>
                )}
              </div>
            </div>
            <div>
              <label className="block mb-2 text-sm font-bold text-slate-600">Từ khóa (Tags)</label>
              <input type="text" className="w-full p-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="reactjs, tutorial..." value={courseDetails.tags} onChange={(e) => setCourseDetails({...courseDetails, tags: e.target.value})} />
            </div>

            <div>
              <label className="block mb-1 text-xs font-bold text-slate-600">
                Giá (VNĐ)
              </label>
              <input
                type="number"
                min="0"
                disabled={isAllLessonsFree}
                className={`w-full p-2.5 text-sm border rounded-lg outline-none transition-all ${isAllLessonsFree ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200' : 'focus:ring-2 focus:ring-blue-500'}`} 
                placeholder="VD: 500000 — để trống nếu miễn phí"
                value={courseDetails.price}
                onChange={(e) => setCourseDetails({ ...courseDetails, price: e.target.value })}
              />
            </div>
            {isAllLessonsFree && (
              <div className="text-[11px] font-bold text-orange-500 bg-orange-50 p-2 rounded-lg border border-orange-100">
                🔒 Chức năng nhập giá bị khóa do TẤT CẢ bài học đang được thiết lập là "Học thử (Free)".
              </div>
            )}
            <div className="pt-4">
              <button onClick={handleSaveDraft} className="w-full py-3.5 font-bold text-white transition-all bg-blue-600 rounded-xl shadow-lg hover:bg-blue-700 active:scale-[0.98] cursor-pointer">
                LƯU THÔNG TIN
              </button>
            </div>
          </div>
        )}
      </div>

    </div>
  );
}