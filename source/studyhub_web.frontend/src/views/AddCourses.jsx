import React from 'react';
import './Courses.css';

const AddCourses = ({ onClose }) => {
  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <h2>Create new course</h2>
        
        <form className="course-form">
          <input type="text" placeholder="Enter course name" className="form-input" />
          
          <div className="form-row">
            <select className="form-select">
            </select>
            <select className="form-select">
            </select>
          </div>

          {/* Thanh công cụ soạn thảo giả lập */}
          <div className="rich-text-editor">
            <div className="editor-toolbar">
              <span>Paragraph ⌄</span>
              <strong>B</strong>
              <em>I</em>
              <u>U</u>
              <span>A ⌄</span>
              <span>✎ ⌄</span>
              <span>•••</span>
            </div>
            <textarea placeholder="Course description..." className="editor-textarea"></textarea>
          </div>

          <div className="image-upload-section">
            <div className="upload-box">
              <span className="upload-icon">↑</span> Upload Image
            </div>
            <div className="upload-instructions">
            </div>
          </div>

          <div className="modal-footer">
            <label className="checkbox-label">
              <input type="checkbox" defaultChecked /> Publish
            </label>
            <div className="action-buttons">
              <button type="button" className="btn-cancel" onClick={onClose}>Cancel</button>
              <button type="submit" className="btn-save">Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddCourses;