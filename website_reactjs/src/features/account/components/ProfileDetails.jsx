import React, { useState } from 'react';
import { User, Mail, Phone, Lock } from 'lucide-react';
import Card from '../../../components/common/Card';
import useSettingsStore from '../../../store/useSettingsStore';


const ProfileDetails = ({ user }) => {
  const t = useSettingsStore(state => state.t);
  const [formData, setFormData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  });

  return (
    <div className="space-y-8">
      <Card className="p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
        <Card.Header className="px-0 pt-0 border-none">
          <Card.Title className="text-2xl font-black tracking-tight">{t('account.profile_title')}</Card.Title>
        </Card.Header>
        
        <Card.Body className="px-0 pb-0">
          <form className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-sm font-bold text-slate-700">{t('account.full_name')}</label>

              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                  <User size={18} />
                </span>
                <input 
                  type="text" 
                  value={formData.name}
                  onChange={(e) => setFormData({...formData, name: e.target.value})}
                  className="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-bold text-slate-700">{t('account.email')}</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                  <Mail size={18} />
                </span>
                <input 
                  type="email" 
                  value={formData.email}
                  disabled
                  className="w-full pl-12 pr-4 py-3 bg-slate-100 border border-slate-200 rounded-2xl text-slate-500 cursor-not-allowed"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-bold text-slate-700">{t('account.phone')}</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                  <Phone size={18} />
                </span>
                <input 
                  type="tel" 
                  value={formData.phone}
                  onChange={(e) => setFormData({...formData, phone: e.target.value})}
                  className="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                />
              </div>
            </div>

            <div className="md:col-span-2 pt-4">
              <button className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-2xl transition-all shadow-lg shadow-blue-100 hover:shadow-blue-200">
                {t('account.save_changes')}
              </button>
            </div>
          </form>
        </Card.Body>
      </Card>

      <Card className="p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
        <Card.Header className="px-0 pt-0 border-none">
          <Card.Title className="text-2xl font-black tracking-tight text-red-600">{t('account.password_title')}</Card.Title>
        </Card.Header>
        
        <Card.Body className="px-0 pb-0">
          <form className="space-y-6 max-w-md">
            <div className="space-y-2">
              <label className="text-sm font-bold text-slate-700">{t('account.current_password')}</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                  <Lock size={18} />
                </span>
                <input 
                  type="password" 
                  className="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-bold text-slate-700">{t('account.new_password')}</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                  <Lock size={18} />
                </span>
                <input 
                  type="password" 
                  className="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-bold text-slate-700">{t('account.confirm_password')}</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                  <Lock size={18} />
                </span>
                <input 
                  type="password" 
                  className="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
                />
              </div>
            </div>

            <button className="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-8 rounded-2xl transition-all shadow-lg shadow-slate-100">
              {t('account.update_password')}
            </button>
          </form>
        </Card.Body>
      </Card>
    </div>
  );
};

export default ProfileDetails;
