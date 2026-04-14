import React from 'react';

const BlockEditor = ({ lessonTitle, blocks, setBlocks }) => {
  
  // Hàm thêm khối mới
  const addBlock = (type) => {
    const newBlock = { id: Date.now().toString(), type: type, content: '' };
    setBlocks([...blocks, newBlock]);
  };

  // Hàm xóa khối
  const removeBlock = (id) => {
    setBlocks(blocks.filter(block => block.id !== id));
  };

  // Hàm cập nhật nội dung khối
  const updateBlockContent = (id, newContent) => {
    setBlocks(blocks.map(block => block.id === id ? { ...block, content: newContent } : block));
  };

  return (
    <div className="flex flex-col w-full overflow-y-auto bg-slate-50 relative h-full">
      <div className="max-w-7xl w-full mx-auto p-8 pb-32">
        {/* Tiêu đề bài học đang soạn thảo */}
        <h1 className="mb-8 text-3xl font-bold text-slate-800 border-b pb-4">
          {lessonTitle || "Chọn một bài học để soạn thảo"}
        </h1>
        
        {/* Danh sách các khối */}
        <div className="space-y-6">
          {blocks.map((block) => (
            <div key={block.id} className="relative group bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:border-blue-300 transition-colors">
              
              {/* Nút xóa khối */}
              <button 
                onClick={() => removeBlock(block.id)}
                className="absolute top-3 right-3 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity p-1 bg-red-50 rounded"
              >
                X
              </button>

              {/* Logic Render từng loại Block */}
              {block.type === 'text' && (
                <div>
                  <span className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Văn Bản</span>
                  <textarea 
                    className="w-full min-h-[120px] p-0 border-none resize-none focus:ring-0 text-slate-700 outline-none"
                    placeholder="Bắt đầu viết nội dung bài giảng..."
                    value={block.content}
                    onChange={(e) => updateBlockContent(block.id, e.target.value)}
                  />
                </div>
              )}

              {block.type === 'video' && (
                <div>
                  <span className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Video</span>
                  <div className="flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-lg border-slate-300 bg-slate-50 hover:bg-slate-100 cursor-pointer transition">
                    <span className="text-3xl mb-2">☁️</span>
                    <p className="text-sm text-slate-500 font-medium">Tải lên video bài giảng</p>
                  </div>
                </div>
              )}

              {block.type === 'image' && (
                <div>
                  <span className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Hình Ảnh</span>
                  <div className="flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-lg border-slate-300 bg-slate-50 hover:bg-slate-100 cursor-pointer transition">
                    <p className="text-sm text-slate-500 font-medium">📸 Chọn hình ảnh minh họa</p>
                  </div>
                </div>
              )}

              {block.type === 'quiz' && (
                <div>
                  <span className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Bài Tập</span>
                  <div className="space-y-3">
                    <input type="text" placeholder="Nhập câu hỏi..." className="w-full p-2 border rounded font-medium focus:outline-blue-500" />
                    <button className="text-sm text-blue-500 hover:underline">+ Thêm phương án trả lời</button>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>

        {/* Floating Toolbar: Công cụ thêm khối */}
        <div className="sticky bottom-6 mt-10 p-3 bg-white border shadow-2xl rounded-full flex justify-center gap-2 w-max mx-auto z-10 border-blue-100">
          <button onClick={() => addBlock('text')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all">
            + Văn bản
          </button>
          <button onClick={() => addBlock('video')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all">
            + Video
          </button>
          <button onClick={() => addBlock('image')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all">
            + Ảnh
          </button>
          <button onClick={() => addBlock('quiz')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all">
            + Bài tập
          </button>
        </div>
      </div>
    </div>
  );
};

export default BlockEditor;