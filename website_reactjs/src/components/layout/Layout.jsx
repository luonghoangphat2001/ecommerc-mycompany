import React from 'react';
import { Outlet } from 'react-router-dom';
import Header from './Header';
import Footer from './Footer';

const Layout = () => {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans relative overflow-x-hidden">
      <div className="fixed top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-400 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>
      <div className="fixed bottom-[-20%] left-[-10%] w-[500px] h-[500px] bg-purple-400 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>

      <Header />
      
      <main className="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 z-10">
        <Outlet />
      </main>

      <Footer />
    </div>
  );
};

export default Layout;
