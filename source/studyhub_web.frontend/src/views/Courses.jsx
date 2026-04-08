import React, { useState } from 'react';
import './Courses.css';
import MainLayout from '../layouts/MainLayout';
import CourseModal from './AddCourses';

const Courses = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  
  // Dữ liệu mẫu (mock data)
  const [courses, setCourses] = useState([
    {
      id: 1,
      title: 'Lập trình C nâng cao',
      description: 'Bài tập này giúp học sinh biết từ cơ bản đến nâng cao',
      image: 'src/assets/images/logo_laptrinhC.jpg', // Thay bằng link ảnh thật
      status: 'Draft',
      students: 0
    }
  ]);

  return (
    <MainLayout>
    <div className="courses-page">
      {/* Tabs & Header */}
      <div className="courses-header">
        <div className="tabs">
          <button className="tab active">List course ({courses.length} Courses)</button>
        </div>
      </div>

      {/* Toolbar (Search & Add Button) */}
      <div className="toolbar">
        <div className="search-box">
          <span className="search-icon">🔍</span>
          <input type="text" placeholder="Search" />
        </div>
        <button className="btn-add" onClick={() => setIsModalOpen(true)}>
          + Add new course
        </button>
      </div>

      {/* Course Grid */}
      <div className="course-grid">
        {courses.map(course => (
          <div key={course.id} className="course-card">
            <div className="card-image-wrapper">
              <img src={course.image} alt={course.title} />
              <span className="status-badge">{course.status}</span>
            </div>
            <div className="card-content">
              <h3>{course.title}</h3>
              <p>{course.description}</p>
              <div className="card-stats">
              </div>
              <button className="btn-view">View Lesson</button>
            </div>
          </div>
        ))}
      </div>

      {/* Gọi Modal Component */}
      {isModalOpen && <CourseModal onClose={() => setIsModalOpen(false)} />}
    </div>
    </MainLayout>
  );
};

export default Courses;