import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import MainLayout from "./layouts/MainLayout";
import Login from './views/Login';
import Register from './views/Register';
import Dashboard from "./views/Dashboard";
import Courses from './views/Courses';
import CourseEditor from './views/CourseEditor';
import PublishedCourses from './views/PublishedCourses';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        
        {/* KHÔNG GIAN HỌC TẬP (Chế độ Học viên) */}
        <Route path="/student" element={<MainLayout mode="student" />}>
          <Route path="dashboard" element={<div className="p-8">Trang chủ học viên</div>} />
          <Route path="my-courses" element={<div className="p-8">Khóa học tôi đang học</div>} />
          <Route path="documents" element={<div className="p-8">Tài liệu tham khảo</div>} />
        </Route>

        {/* KHÔNG GIAN GIẢNG DẠY (Chế độ Giảng viên) */}
        <Route path="/lecturer" element={<MainLayout mode="lecturer" />}>
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="courses" element={<Courses />} /> 
          <Route path="courses/:courseId/edit" element={<CourseEditor />} />
          <Route path="published-courses" element={<PublishedCourses />} />
          <Route path="students" element={<div className="p-8">Quản lý học viên của tôi</div>} />
        </Route>

        {/* Mặc định vào app sẽ đẩy vào không gian học tập */}
        <Route path="/" element={<Navigate to="/student/dashboard" replace />} />
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;