import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import BlockEditor from '../components/BlockEditor'; 
import CourseAPI from '../services/courseApi'; 

export default function CourseEditor() {
  const { courseId } = useParams();
  const navigate = useNavigate();

  const [courseDetails, setCourseDetails] = useState({ title: 'Đang tải...', description: '', thumbnail: null, tags: '', price: '', discountPrice: '' });
  const [isRightSidebarOpen, setIsRightSidebarOpen] = useState(true);
  const [courseData, setCourseData] = useState([]);
  const [activeItem, setActiveItem] = useState(null);
  const [expandedUnits, setExpandedUnits] = useState({});
  const [blocks, setBlocks] = useState([]);
  
  // 1. STATE QUẢN LÝ TRẠNG THÁI LOADING KHI ĐANG TẢI ẢNH
  const [isUploadingThumb, setIsUploadingThumb] = useState(false);

  const toggleUnit = (unitId) => setExpandedUnits(prev => ({ ...prev, [unitId]: !prev[unitId] }));
  
  const handleAddUnit = () => {
    const title = window.prompt("Nhập tên Unit mới:");
    if (title?.trim()) {
      const newId = `u${Date.now()}`;
      setCourseData([...courseData, { id: newId, title, items: [] }]);
      setExpandedUnits(prev => ({ ...prev, [newId]: true }));
    }
  };

  const handleAddLesson = (unitId) => {
    const title = window.prompt("Nhập tên bài học mới:");
    if (title?.trim()) {
      setCourseData(courseData.map(u => u.id === unitId ? { ...u, items: [...u.items, { id: `l${Date.now()}`, title }] } : u));
    }
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
          discountPrice: draft.discountPrice || ''
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

        setBlocks(draft.blocks || []);

      } catch (error) {
        console.error("Lỗi lấy dữ liệu bản nháp:", error);
      }
    };

    if (courseId) {
      fetchDraftData();
    }
  }, [courseId]);

  // 2. HÀM XỬ LÝ UPLOAD THUMBNAIL
  const handleThumbnailUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setIsUploadingThumb(true);
    try {
      // Tái sử dụng lại API uploadImage đã viết ở phần Block Ảnh
      const response = await CourseAPI.uploadImage(file);
      const imageUrl = response.data.imageUrl;
      
      // Cập nhật đường dẫn URL vào state courseDetails
      setCourseDetails({ ...courseDetails, thumbnail: imageUrl });
    } catch (error) {
      console.error("Lỗi tải ảnh:", error);
      alert("Không thể tải ảnh lên. Vui lòng kiểm tra lại định dạng hoặc dung lượng!");
    } finally {
      setIsUploadingThumb(false);
    }
  };

  const handleSaveDraft = async () => {
    try {
      const payload = {
        title: courseDetails.title || '',
        description: courseDetails.description || '',
        thumbnail: courseDetails.thumbnail || null,
        tags: courseDetails.tags || '',
        price: Number(courseDetails.price) || 0,
        discountPrice: Number(courseDetails.discountPrice) || 0,
        courseData: courseData || [], 
        blocks: blocks || []

      };

      await CourseAPI.updateDraft(courseId, payload);
      alert("Đã lưu bản nháp thành công!");
      navigate('/lecturer/courses');
    } catch (error) {
      console.error("Lỗi khi lưu bản nháp:", error.response?.data);
      alert("Lưu thất bại: " + (error.response?.data?.message || "Vui lòng xem chi tiết lỗi ở F12 -> Console"));
    }
  };

  return (
    <div className="flex h-[calc(100vh-64px)] bg-slate-50 border-t overflow-hidden">
      
      {/* CỘT TRÁI: Cấu trúc khóa học */}
      <div className="flex flex-col w-1/5 bg-white border-r shadow-sm transition-all duration-300">
        <div className="flex items-center justify-between p-4 font-bold border-b bg-slate-50 text-slate-700 text-sm">
          <span>Nội dung</span>
          <button onClick={handleAddUnit} className="px-2 py-1 text-[10px] font-semibold text-blue-600 bg-blue-100 rounded hover:bg-blue-600 hover:text-white transition-all">+ Unit</button>
        </div>
        <div className="flex-1 p-4 overflow-y-auto">
          {courseData.length === 0 && (
            <p className="text-xs text-slate-400 text-center mt-10">Chưa có nội dung.<br/>Hãy bấm "+ Unit" để bắt đầu.</p>
          )}
          {courseData.map(unit => (
            <div key={unit.id} className="mb-3 overflow-hidden border rounded">
              <div onClick={() => toggleUnit(unit.id)} className="flex items-center justify-between p-3 text-xs font-medium transition-colors cursor-pointer bg-slate-100 hover:bg-slate-200 group">
                <div className="flex items-center gap-2">
                  <span className="w-4 text-[10px] text-slate-400">{expandedUnits[unit.id] ? '▼' : '▶'}</span>
                  <span>{unit.title}</span>
                </div>
              </div>
              {expandedUnits[unit.id] && (
                <div className="bg-white">
                  {unit.items.map(item => (
                    <div key={item.id} onClick={() => setActiveItem(item)} className={`p-2.5 pl-8 text-xs cursor-pointer hover:bg-blue-50 transition-all ${activeItem?.id === item.id ? 'bg-blue-100 border-l-4 border-blue-600 font-medium' : ''}`}>
                      {item.title}
                    </div>
                  ))}
                  <div onClick={() => handleAddLesson(unit.id)} className="p-2 pl-8 text-[10px] font-medium text-blue-500 cursor-pointer hover:bg-slate-50">+ Thêm bài học...</div>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* CỘT GIỮA: TRÌNH SOẠN THẢO */}
      <div className="flex-1 flex flex-col border-r shadow-inner bg-white overflow-hidden transition-all duration-300">
        {activeItem ? (
          <BlockEditor lessonTitle={activeItem.title} blocks={blocks} setBlocks={setBlocks} />
        ) : (
          <div className="flex flex-col items-center justify-center h-full text-slate-400 font-medium space-y-2">
            <span className="text-4xl opacity-20">📝</span>
            <p>Chọn hoặc tạo một bài học để bắt đầu soạn thảo</p>
          </div>
        )}
      </div>

      {/* CỘT PHẢI: THIẾT LẬP KHÓA HỌC */}
      <div className={`${isRightSidebarOpen ? 'w-1/5' : 'w-12'} transition-all duration-300 flex bg-white shadow-sm border-l overflow-hidden`}>
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
            
            {/* 3. LOGIC HIỂN THỊ VÀ UPLOAD THUMBNAIL MỚI */}
            <div>
              <label className="block mb-2 text-sm font-bold text-slate-600">Ảnh đại diện</label>
              
              <div className="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl bg-slate-50 border-slate-300 hover:bg-blue-50 transition-all overflow-hidden group">
                {isUploadingThumb ? (
                  <span className="text-sm font-bold text-blue-500 animate-pulse">ĐANG TẢI...</span>
                ) : courseDetails.thumbnail ? (
                  <>
                    {/* object-cover giúp ảnh tự căn chỉnh lấp đầy khung mà không bị méo */}
                    <img src={courseDetails.thumbnail} alt="Thumbnail" className="object-cover w-full h-full" />
                    
                    {/* Lớp phủ (Overlay) hiện ra khi hover chuột vào ảnh để đổi ảnh khác */}
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

            <div className="flex gap-3">
              <div className="flex-1">
                <label className="block mb-2 text-xs font-bold text-slate-600">Giá gốc (VNĐ)</label>
                <input 
                  type="number" 
                  min="0"
                  className="w-full p-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all" 
                  placeholder="VD: 500000" 
                  value={courseDetails.price} 
                  onChange={(e) => setCourseDetails({...courseDetails, price: e.target.value})} 
                />
              </div>
              <div className="flex-1">
                <label className="block mb-2 text-xs font-bold text-slate-600">Giá giảm (VNĐ)</label>
                <input 
                  type="number" 
                  min="0"
                  className="w-full p-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all" 
                  placeholder="VD: 399000" 
                  value={courseDetails.discountPrice} 
                  onChange={(e) => setCourseDetails({...courseDetails, discountPrice: e.target.value})} 
                />
              </div>
            </div>

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