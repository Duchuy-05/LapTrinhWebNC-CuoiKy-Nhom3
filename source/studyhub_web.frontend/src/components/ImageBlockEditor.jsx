import React, { useState } from 'react';
import CourseAPI from '../services/courseApi';

const ImageBlockEditor = ({ block, updateBlock }) => {
  const [imageUrl, setImageUrl] = useState(block.content || '');
  const [isUploading, setIsUploading] = useState(false);

  const handleFileChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setIsUploading(true);
    try {
      const response = await CourseAPI.uploadImage(file);
      const url = response.data.imageUrl;
      
      setImageUrl(url);
      // Cập nhật URL vào content của block ở component cha
      updateBlock(block.id, { content: url });
      
    } catch (error) {
      console.error(error);
      alert("Lỗi khi tải ảnh lên.");
    } finally {
      setIsUploading(false);
    }
  };

  return (
    <div className="mt-2">
      <div className={`relative border-2 border-dashed rounded-xl p-4 transition-all ${imageUrl ? 'border-solid border-slate-200' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'}`}>
        {isUploading ? (
          <div className="py-10 text-center">
            <p className="text-blue-500 font-bold animate-pulse">ĐANG TẢI ẢNH...</p>
          </div>
        ) : imageUrl ? (
          <div className="group relative">
            <img 
              src={imageUrl} 
              alt="Preview" 
              className="w-full h-auto max-h-[400px] object-contain rounded-lg shadow-sm"
            />
            <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
              <label className="bg-white px-4 py-2 rounded-lg text-sm font-bold cursor-pointer hover:bg-slate-100">
                Thay đổi ảnh
                <input type="file" className="hidden" accept="image/*" onChange={handleFileChange} />
              </label>
              <button 
                onClick={() => { setImageUrl(''); updateBlock(block.id, { content: '' }); }}
                className="ml-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-700"
              >
                Xóa
              </button>
            </div>
          </div>
        ) : (
          <label className="flex flex-col items-center justify-center py-10 cursor-pointer">
            <span className="text-4xl mb-2">📸</span>
            <p className="text-sm text-slate-500 font-medium">Nhấp để chọn hình ảnh bài giảng</p>
            <p className="text-xs text-slate-400 mt-1">Hỗ trợ: JPG, PNG, GIF (Tối đa 2MB)</p>
            <input type="file" className="hidden" accept="image/*" onChange={handleFileChange} />
          </label>
        )}
      </div>
    </div>
  );
};

export default ImageBlockEditor;