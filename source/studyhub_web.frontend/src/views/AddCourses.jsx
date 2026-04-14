import React from 'react';

const AddCourses = ({ onClose }) => {
  return (
    <div className="fixed inset-0 z-[1000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div className="w-full max-w-2xl p-8 bg-white shadow-2xl rounded-3xl animate-[slideUpFade_0.3s_ease-out]">
        <h2 className="mb-6 text-2xl font-bold text-gray-800">Create new course</h2>
        
        <form className="flex flex-col gap-5">
          <input type="text" placeholder="Enter course name" className="w-full p-3.5 border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" />
          
          <div className="flex gap-5">
            <select className="w-full p-3.5 border border-slate-200 rounded-xl outline-none bg-white"></select>
            <select className="w-full p-3.5 border border-slate-200 rounded-xl outline-none bg-white"></select>
          </div>

          {/* Trình soạn thảo */}
          <div className="overflow-hidden border border-slate-200 rounded-xl focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10 transition-all">
            <div className="flex gap-4 p-3 bg-slate-50 border-b border-slate-200 text-slate-600">
              <span className="font-semibold cursor-pointer hover:text-black">Paragraph ⌄</span>
              <strong className="cursor-pointer hover:text-black">B</strong>
              <em className="cursor-pointer hover:text-black">I</em>
              <u className="cursor-pointer hover:text-black">U</u>
            </div>
            <textarea placeholder="Course description..." className="w-full h-32 p-4 outline-none resize-none"></textarea>
          </div>

          {/* Upload Ảnh */}
          <div className="flex items-center gap-5">
            <div className="flex flex-col items-center justify-center w-1/2 h-32 font-bold text-blue-600 transition-colors border-2 border-dashed cursor-pointer border-slate-300 rounded-xl hover:bg-slate-50 hover:border-blue-400">
              <span className="text-2xl">↑</span> Upload Image
            </div>
            <div className="w-1/2 text-sm text-slate-500">
              Supports JPG, PNG. Max size 5MB.
            </div>
          </div>

          {/* Nút lưu */}
          <div className="flex items-center justify-between mt-4">
            <label className="flex items-center gap-2 font-medium cursor-pointer text-slate-700">
              <input type="checkbox" defaultChecked className="w-4 h-4 accent-blue-600" /> Publish
            </label>
            <div className="flex gap-3">
              <button type="button" onClick={onClose} className="px-6 py-2.5 font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Cancel</button>
              <button type="submit" className="px-6 py-2.5 font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/30">Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddCourses;