import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import AuthLayout from "./layouts/AuthLayout";
import AppLayout from "./layouts/AppLayout";
import Login from './views/Login';
import Register from './views/Register';
import Dashboard from "./views/Dashboard";
import Courses from './views/Courses';
function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="login" element={<Login />} />
        <Route path="register" element={<Register />} />

        {/* Route App: Bảo vệ (cho các trang sau khi đăng nhập) */}
        <Route path="/" element={<AppLayout />}>
          {/* Dashboard (Route con của AppLayout) */}
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="homework" element={<div className="flex-1 p-8 text-center bg-gray-50">Homework View - Placeholder</div>} />
          <Route path="exams" element={<div className="flex-1 p-8 text-center bg-gray-50">Exams View - Placeholder</div>} />
          <Route path="classes" element={<div className="flex-1 p-8 text-center bg-gray-50">Classes View - Placeholder</div>} />
          <Route path="/courses" element={<Courses />} />
        </Route>
       { /* Nếu truy cập đường dẫn không tồn tại, tự động chuyển về trang login*/}
        <Route path="*" element={<Navigate to="/login" replace />} />

      </Routes>
    </BrowserRouter>
  );
}

export default App;