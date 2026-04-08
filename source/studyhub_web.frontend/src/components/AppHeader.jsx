import React from 'react';
import './AppHeader.css';
import StudyHubLogo from '../assets/images/logo_Studyhub.jpg';
import { NavLink } from 'react-router-dom';
const AppHeader = () => {
  return (
    <aside className="app-sidebar">
        <div className="logo" data-tooltip="StudyHub Logo">
          <img src={StudyHubLogo} alt="StudyHub Logo" className="logo-icon" /> 
        </div>
      <div className="sidebar-top">  
        <nav className="side-nav">
          <NavLink to="/" className="nav-item active" data-tooltip="Home">🏠</NavLink>
          <NavLink to="/search" className="nav-item" data-tooltip="Search">🔍</NavLink>
          <NavLink to="/assignments" className="nav-item" data-tooltip="Assignments">📄</NavLink>
          <NavLink to="/documents" className="nav-item" data-tooltip="Documents">📁</NavLink>
          <NavLink to="/classes" className="nav-item" data-tooltip="Classes">📚</NavLink>
          <NavLink to="/courses" className="nav-item" data-tooltip="Courses">📘</NavLink>
        </nav>
      </div>
{/* Phía dưới cùng: Ngôn ngữ, Thông báo, User và Cài đặt */}
      <div className="sidebar-bottom">
        <button className="nav-item btn-language">
          EN
        </button>
        <button className="nav-item btn-icon">🔔</button>
        <div className="nav-item user-profile">
          <div className="avatar">H</div>
        </div>
        <a href="#" className="nav-item settings-icon">⚙️</a>
      </div>
    </aside>
  );
};

export default AppHeader;