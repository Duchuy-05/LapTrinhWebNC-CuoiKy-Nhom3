import React from 'react';
import VideoBlockEditor from './VideoBlockEditor'; // Import component con
import ImageBlockEditor from './ImageBlockEditor';
import QuizBlockEditor from './QuizBlockEditor';
const BlockEditor = ({ lessonTitle, blocks, setBlocks }) => {
  
  // Hàm thêm khối mới
const addBlock = (type) => {
    const newBlock = { 
      id: Date.now().toString(), 
      type: type, 
      content: '', 
      // Khởi tạo data rỗng cho video
      videoData: type === 'video' ? {} : null 
    };
    setBlocks([...blocks, newBlock]);
  };

 // Hàm xóa khối
const removeBlock = (id) => {
    setBlocks(blocks.filter(block => block.id !== id));
  };

// hàm cập nhật 
const updateBlockData = (id, data) => {
    setBlocks(blocks.map(block => 
      // Nếu là text thì data là chuỗi, nếu là video thì data là object
      block.id === id ? { ...block, ...data } : block
    ));
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
                    value={block.content || ''}
                    onChange={(e) => updateBlockContent(block.id, e.target.value)}
                  />
                </div>
              )}
              {/*// Logic render cho block video sẽ được xử lý bởi component con VideoBlockEditor*/ }
              {block.type === 'video' && (
                <div>
                  <span className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Video</span>
                  <VideoBlockEditor 
                    block={block} 
                    updateBlock={updateBlockData} 
                  />
                </div>
              )}

              {block.type === 'image' && (
                <div>
                  <span className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Hình Ảnh</span>
                  <ImageBlockEditor 
                    block={block} 
                    updateBlock={updateBlockData} 
                  />
                </div>
              )}

              {block.type === 'quiz' && (
                <div>
                  <span className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Bài Tập Trắc Nghiệm</span>
                  <QuizBlockEditor 
                    block={block} 
                    updateBlock={updateBlockData} 
                  />
                </div>
              )}
            </div>
          ))}
        </div>

        {/* Floating Toolbar: Công cụ thêm khối */}
        <div className="sticky bottom-6 mt-10 p-3 bg-white border shadow-2xl rounded-full flex justify-center gap-2 w-max mx-auto z-10 border-blue-100">
          <button onClick={() => addBlock('text')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all cursor-pointer">
            + Văn bản
          </button>
          <button onClick={() => addBlock('video')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all cursor-pointer ">
            + Video
          </button>
          <button onClick={() => addBlock('image')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all cursor-pointer">
            + Ảnh
          </button>
          <button onClick={() => addBlock('quiz')} className="flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-full hover:bg-blue-600 hover:text-white transition-all cursor-pointer">
            + Bài tập
          </button>
        </div>
      </div>
    </div>
  );
};

export default BlockEditor;