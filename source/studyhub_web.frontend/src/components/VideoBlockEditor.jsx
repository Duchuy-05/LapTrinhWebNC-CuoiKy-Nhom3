import React, { useState } from 'react';
import CourseAPI from '../services/courseApi'; 

const VideoBlockEditor = ({ block, updateBlock }) => {
  // Xác định tab mặc định từ dữ liệu đã lưu
  const [activeTab, setActiveTab] = useState(block.videoType || 'link');
  
  // 1. STATE RIÊNG BIỆT CHO TỪNG CHỨC NĂNG
  const initialVideoData = {
    // Dữ liệu cho Tab Link Youtube
    youtubeUrl: block.videoType === 'link' ? (block.url || '') : '',
    youtubeTitle: block.videoType === 'link' ? (block.title || '') : '',
    youtubeDuration: block.videoType === 'link' ? (block.duration || 2) : 2,
    
    // Dữ liệu cho Tab Upload máy tính
    uploadUrl: block.videoType === 'upload' ? (block.url || '') : '',
    uploadTitle: block.videoType === 'upload' ? (block.title || '') : '',
    uploadDuration: block.videoType === 'upload' ? (block.duration || 2) : 2,
  };

  const [videoData, setVideoData] = useState(initialVideoData);
  const [isUploading, setIsUploading] = useState(false);

  // Trích xuất ID Youtube để preview
  const getYouTubeVideoId = (url) => {
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
  };

  const handleChange = (field, value) => {
    setVideoData((prevData) => ({ ...prevData, [field]: value }));
  };

  // 2. LOGIC LƯU RIÊNG BIỆT
  const handleSave = () => {
    let dataToUpdate = {};

    if (activeTab === 'link') {
      dataToUpdate = {
        url: videoData.youtubeUrl,
        title: videoData.youtubeTitle,
        duration: videoData.youtubeDuration,
        videoType: 'link'
      };
    } else {
      dataToUpdate = {
        url: videoData.uploadUrl,
        title: videoData.uploadTitle,
        duration: videoData.uploadDuration,
        videoType: 'upload'
      };
    }

    updateBlock(block.id, dataToUpdate);
  };

  const handleCancel = () => {
    setVideoData(initialVideoData);
    setActiveTab(block.videoType || 'link');
  };

  const handleFileUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setIsUploading(true);
    try {
      const response = await CourseAPI.uploadVideo(file);
      const uploadedUrl = response.data.videoUrl; 
      
      handleChange('uploadUrl', uploadedUrl);
      
      // Tự động gợi ý tiêu đề từ tên file cho phần Upload
      if (!videoData.uploadTitle) {
        handleChange('uploadTitle', file.name.split('.').slice(0, -1).join('.'));
      }
      
    } catch (error) {
      console.error(error);
      alert("Lỗi tải file.");
    } finally {
      setIsUploading(false);
    }
  };

  return (
    <div className="border border-slate-200 rounded-lg p-5 bg-white mt-2 shadow-sm">
      {/* Tabs Menu */}
      <div className="flex gap-6 mb-6 border-b border-slate-100 pb-2">
        <button 
          onClick={() => setActiveTab('link')}
          className={`pb-2 text-sm font-bold transition-all ${activeTab === 'link' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 hover:text-slate-600'}`}
        >
          LINK YOUTUBE
        </button>
        <button 
          onClick={() => setActiveTab('upload')}
          className={`pb-2 text-sm font-bold transition-all ${activeTab === 'upload' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 hover:text-slate-600'}`}
        >
          UPLOAD FILE
        </button>
      </div>

      <div className="space-y-5">
        {/* ===================== GIAO DIỆN TAB LINK ===================== */}
        {activeTab === 'link' && (
          <div className="animate-fadeIn space-y-4">
             {getYouTubeVideoId(videoData.youtubeUrl) && (
               <div className="relative w-full aspect-video rounded-lg overflow-hidden bg-black shadow-lg">
                 <iframe
                   className="absolute top-0 left-0 w-full h-full"
                   src={`https://www.youtube.com/embed/${getYouTubeVideoId(videoData.youtubeUrl)}`}
                   frameBorder="0"
                   allowFullScreen
                 ></iframe>
               </div>
             )}
             
             <div className="space-y-3">
               <label className="text-xs font-bold text-slate-500">ĐƯỜNG DẪN VIDEO</label>
               <input 
                 type="text" 
                 placeholder="Dán link Youtube tại đây..." 
                 className="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-blue-100 outline-none border-slate-200"
                 value={videoData.youtubeUrl}
                 onChange={(e) => handleChange('youtubeUrl', e.target.value)}
               />
               
               <div className="flex gap-4">
                 <div className="flex-1 space-y-2">
                   <label className="text-xs font-bold text-slate-500">TIÊU ĐỀ BÀI HỌC</label>
                   <input 
                     type="text" 
                     placeholder="Nhập tên tiêu đề nhé..." 
                     className="w-full p-2.5 border rounded-lg outline-none border-slate-200 focus:border-blue-400"
                     value={videoData.youtubeTitle}
                     onChange={(e) => handleChange('youtubeTitle', e.target.value)}
                   />
                 </div>
                 <div className="w-32 space-y-2">
                   <label className="text-xs font-bold text-slate-500">THỜI LƯỢNG</label>
                   <div className="flex items-center border rounded-lg overflow-hidden border-slate-200">
                     <input 
                       type="number"
                       // thời gian tối thiểu 1 phút
                       min={1}
                       className="w-full p-2.5 outline-none text-center"
                       value={videoData.youtubeDuration}
                       onChange={(e) => handleChange('youtubeDuration', Math.max(1, Number(e.target.value)))} 
                     />
                     <span className="bg-slate-50 px-2 text-xs font-bold text-slate-400">P</span>
                   </div>
                 </div>
               </div>
             </div>
          </div>
        )}

        {/* ===================== GIAO DIỆN TAB UPLOAD ===================== */}
        {activeTab === 'upload' && (
          <div className="animate-fadeIn space-y-4">
             <div className={`border-2 border-dashed rounded-lg p-6 text-center transition-all ${videoData.uploadUrl ? 'border-green-200 bg-green-50/30' : 'border-slate-200 bg-slate-50 hover:bg-slate-100'}`}>
                {isUploading ? (
                  <p className="text-blue-500 font-bold animate-pulse">ĐANG TẢI LÊN...</p>
                ) : videoData.uploadUrl ? (
                  <div className="space-y-3">
                    <video src={videoData.uploadUrl} controls className="w-full rounded-lg shadow-md aspect-video bg-black" />
                    <button 
                      onClick={() => handleChange('uploadUrl', '')}
                      className="text-xs text-red-500 font-bold hover:underline"
                    >
                      XÓA VÀ TẢI LẠI FILE KHÁC
                    </button>
                  </div>
                ) : (
                  <label className="cursor-pointer block">
                    <p className="text-slate-500 font-medium">Chọn file video từ máy tính</p>
                    <p className="text-xs text-slate-400 mt-1">MP4, WEBM, MOV (Tối đa 40MB)</p>
                    <input type="file" className="hidden" accept="video/*" onChange={handleFileUpload} />
                  </label>
                )}
             </div>

             <div className="flex gap-4">
                <div className="flex-1 space-y-2">
                  <label className="text-xs font-bold text-slate-500">TIÊU ĐỀ BÀI HỌC</label>
                  <input 
                    type="text" 
                    placeholder="Nhập tên tiêu đề nhé..." 
                    className="w-full p-2.5 border rounded-lg outline-none border-slate-200 focus:border-blue-400"
                    value={videoData.uploadTitle}
                    onChange={(e) => handleChange('uploadTitle', e.target.value)}
                  />
                </div>
                <div className="w-32 space-y-2">
                  <label className="text-xs font-bold text-slate-500">THỜI LƯỢNG</label>
                  <div className="flex items-center border rounded-lg overflow-hidden border-slate-200">
                    <input 
                      type="number" 
                      // thời gian tối thiểu 1 phút
                      min={1}
                      className="w-full p-2.5 outline-none text-center"
                      value={videoData.uploadDuration}
                      onChange={(e) => handleChange('uploadDuration', Number(e.target.value))}
                    />
                    <span className="bg-slate-50 px-2 text-xs font-bold text-slate-400">P</span>
                  </div>
                </div>
             </div>
          </div>
        )}
        <p className="text-xs text-slate-400">
          Học viên cần xem hết thời lượng yêu cầu để được tính là hoàn thành.
        </p>
        {/* Footer Actions */}
        <div className="flex justify-end gap-3 pt-6 mt-4 border-t border-slate-50">
          <button 
            onClick={handleCancel}
            className="px-6 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
          >
            HỦY BỎ
          </button>
          <button 
            onClick={handleSave}
            className="px-8 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md shadow-blue-100 transition-all active:scale-95 cursor-pointer"
          >
            LƯU VIDEO
          </button>
        </div>
      </div>
    </div>
  );
};

export default VideoBlockEditor;