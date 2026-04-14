import React from 'react';

const Dashboard = () => {
  const features = [
    { id: 1, title: 'Search', icon: '🔍' },
    { id: 2, title: 'Homework', icon: '📄' },
    { id: 3, title: 'Exams', icon: '📁' },
    { id: 4, title: 'Classes', icon: '📚' },
    { id: 5, title: 'Courses', icon: '📘', isWide: true },
  ];

  return (
    <div className="flex flex-col gap-8 w-full max-w-6xl mx-auto">
      <h1 className="text-3xl font-bold text-center text-gray-800">Home screen</h1>
      
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        {features.map((item) => (
          <div key={item.id} className={`bg-white rounded-2xl p-8 flex items-center justify-center gap-4 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer ${item.isWide ? 'col-span-2 md:col-span-2 flex-row justify-start pl-12' : 'flex-col'}`}>
            <div className="text-4xl">{item.icon}</div>
            <div className="text-lg font-semibold text-gray-700">{item.title}</div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Dashboard;