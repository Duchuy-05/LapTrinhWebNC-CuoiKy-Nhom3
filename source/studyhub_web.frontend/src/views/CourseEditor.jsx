import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import BlockEditor from '../components/BlockEditor'; 
import CourseAPI from '../services/courseApi'; 

export default function CourseEditor() {
  const { courseId } = useParams();

  // 1. XÓA SẠCH DỮ LIỆU MẪU, BẮT ĐẦU VỚI STATE RỖNG
  const [courseDetails, setCourseDetails] = useState({ title: 'Đang tải...', description: '', thumbnail: null, tags: '' });
  const [isRightSidebarOpen, setIsRightSidebarOpen] = useState(true);
  const [courseData, setCourseData] = useState([]);
  const [activeItem, setActiveItem] = useState(null);
  const [expandedUnits, setExpandedUnits] = useState({});
  const [blocks, setBlocks] = useState([]);

  // --- Logic xử lý Unit/Lesson (Giữ nguyên) ---
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

  // 2. KÉO DỮ LIỆU TỪ DB VÀ ÉP GIAO DIỆN HIỂN THỊ CHÍNH XÁC
  useEffect(() => {
    const fetchDraftData = async () => {
      try {
        const response = await CourseAPI.getDraft(courseId);
        const draft = response.data.data;
        
        setCourseDetails({
          title: draft.title || '',
          description: draft.description || '',
          thumbnail: draft.thumbnail || null,
          tags: draft.tags || ''
        });

        // Lấy đúng cấu trúc từ DB (Dù là mảng rỗng)
        const dbCourseData = draft.courseData || [];
        setCourseData(dbCourseData);

        // Mở sẵn các Unit
        const expanded = {};
        dbCourseData.forEach(u => expanded[u.id] = true);
        setExpandedUnits(expanded);

        // Nếu có bài học, tự động chọn bài đầu tiên
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

  // 3. HÀM LƯU DỮ LIỆU ĐƯỢC NÂNG CẤP BẮT LỖI CHI TIẾT
  const handleSaveDraft = async () => {
    try {
      const payload = {
        title: courseDetails.title || '',
        description: courseDetails.description || '',
        thumbnail: courseDetails.thumbnail || null,
        tags: courseDetails.tags || '',
        courseData: courseData || [], 
        blocks: blocks || []          
      };

      await CourseAPI.updateDraft(courseId, payload);
      alert("Đã lưu bản nháp thành công!");
    } catch (error) {
      // Báo lỗi chính xác từ Laravel thay vì báo chung chung
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
              <label className="block mb-2 text-sm font-bold text-slate-600">Mô tả tổng quan</label>
              <textarea className="w-full h-28 p-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none" placeholder="Mô tả tóm tắt về khóa học..." value={courseDetails.description} onChange={(e) => setCourseDetails({...courseDetails, description: e.target.value})}></textarea>
            </div>
            <div>
              <label className="block mb-2 text-sm font-bold text-slate-600">Ảnh đại diện</label>
              <div className="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl bg-slate-50 border-slate-300 hover:bg-blue-50 transition-all cursor-pointer">
                <span className="text-2xl mb-1">📸</span>
                <span className="text-[10px] text-slate-500 font-medium text-center px-4 uppercase">Nhấn để tải ảnh lên</span>
              </div>
            </div>
            <div>
              <label className="block mb-2 text-sm font-bold text-slate-600">Từ khóa (Tags)</label>
              <input type="text" className="w-full p-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="reactjs, tutorial..." value={courseDetails.tags} onChange={(e) => setCourseDetails({...courseDetails, tags: e.target.value})} />
            </div>
            <div className="pt-4">
              <button onClick={handleSaveDraft} className="w-full py-3.5 font-bold text-white transition-all bg-blue-600 rounded-xl shadow-lg hover:bg-blue-700 active:scale-[0.98]">
                LƯU THÔNG TIN
              </button>
            </div>
          </div>
        )}
      </div>

    </div>
  );
}