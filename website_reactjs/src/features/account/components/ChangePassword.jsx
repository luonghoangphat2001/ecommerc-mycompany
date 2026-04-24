import React from 'react';
import { Lock } from 'lucide-react';
import Card from '../../../components/common/Card';

const ChangePassword = () => {
  return (
    <Card className="p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
      <Card.Header className="px-0 pt-0 border-none">
        <Card.Title className="text-2xl font-black tracking-tight">Đổi mật khẩu</Card.Title>
        <p className="text-slate-500 mt-2">Đảm bảo tài khoản của bạn được bảo mật bằng mật khẩu mạnh.</p>
      </Card.Header>
      
      <Card.Body className="px-0 pb-0">
        <form className="space-y-6 max-w-md">
          <div className="space-y-2">
            <label className="text-sm font-bold text-slate-700">Mật khẩu hiện tại</label>
            <div className="relative">
              <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                <Lock size={18} />
              </span>
              <input 
                type="password" 
                className="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                placeholder="Nhập mật khẩu hiện tại"
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-bold text-slate-700">Mật khẩu mới</label>
            <div className="relative">
              <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                <Lock size={18} />
              </span>
              <input 
                type="password" 
                className="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                placeholder="Nhập mật khẩu mới"
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-bold text-slate-700">Xác nhận mật khẩu mới</label>
            <div className="relative">
              <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                <Lock size={18} />
              </span>
              <input 
                type="password" 
                className="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                placeholder="Xác nhận mật khẩu mới"
              />
            </div>
          </div>

          <button className="bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 px-10 rounded-2xl transition-all shadow-lg shadow-slate-100 active:scale-95">
            Cập nhật mật khẩu
          </button>
        </form>
      </Card.Body>
    </Card>
  );
};

export default ChangePassword;
