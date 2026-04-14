import React from 'react';
import { NavLink } from 'react-router-dom';

export default function Sidebar({ menuItems }) {
  return (
    <aside className="fixed left-0 top-16 bottom-0 w-20 md:w-64 bg-slate-50 border-r border-slate-200 flex flex-col py-6 transition-all">
      <nav className="flex flex-col gap-2 px-3">
        {menuItems.map((item, index) => (
          <NavLink 
            key={index} 
            to={item.to} 
            className={({ isActive }) => `
              flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all group
              ${isActive ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white hover:shadow-sm'}
            `}
          >
            <span className="text-2xl">{item.icon}</span>
            <span className="hidden md:block">{item.label}</span>
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}