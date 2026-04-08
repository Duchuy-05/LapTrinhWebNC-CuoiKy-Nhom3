import React from 'react';
import MainLayout from '../layouts/MainLayout';
import './Dashboard.css';

const Dashboard = () => {
  // Danh sách các chức năng (sau này bạn có thể gán link/route vào đây)
  const features = [
    { id: 1, title: 'Search', icon: '🔍' },
    { id: 2, title: 'Homework', icon: '📄' },
    { id: 3, title: 'Exams', icon: '📁' },
    { id: 4, title: 'Classes', icon: '📚' },
    { id: 5, title: 'Courses', icon: '📘' },
  ];

  return (
    <MainLayout>
      <div className="dashboard-container">
        <h1 className="page-title">Home screen</h1>
        
        <div className="grid-container">
          {features.map((item) => (
            <div 
              key={item.id} 
              className={`feature-card ${item.isWide ? 'card-wide' : ''}`}
            >
              <div className="card-icon">{item.icon}</div>
              <div className="card-title">{item.title}</div>
            </div>
          ))}
        </div>
      </div>
    </MainLayout>
  );
};

export default Dashboard;