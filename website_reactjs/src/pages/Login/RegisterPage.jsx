import React from 'react';
import { User, Mail, Lock, Phone } from 'lucide-react';
import { Link } from 'react-router-dom';
import useSettingsStore from '../../store/useSettingsStore';
import useRegisterForm from '../../features/auth/hooks/useRegisterForm';

const inputClass = "w-full pl-12 pr-4 py-3 bg-white/50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-400";
const labelClass = "block text-sm font-semibold text-slate-700 mb-2 ml-1";
const iconContainerClass = "absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors";

const RegisterPage = () => {
  const t = useSettingsStore((state) => state.t);
  const {
    formData,
    setFormData,
    error,
    handleRegister,
    isLoading
  } = useRegisterForm();

  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden font-sans py-12">
      <div className="absolute w-[600px] h-[600px] bg-blue-300 rounded-full blur-[120px] opacity-10 -top-20 -left-20 pointer-events-none"></div>
      <div className="absolute w-[500px] h-[500px] bg-purple-300 rounded-full blur-[120px] opacity-10 -bottom-20 -right-20 pointer-events-none"></div>

      <div className="bg-white/60 backdrop-blur-xl p-8 md:p-12 w-full max-w-[500px] border border-white/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[2.5rem] relative z-10 mx-4">
        <div className="text-center mb-10">
          <h1 className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3 tracking-tight">
            NovaStore
          </h1>
          <p className="text-slate-500 font-medium">{t('auth.register_subtitle')}</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100 font-medium flex items-center">
            {error}
          </div>
        )}

        <form onSubmit={handleRegister} className="space-y-5">
          <div>
            <label className={labelClass}>{t('auth.full_name')}</label>
            <div className="relative group">
              <span className={iconContainerClass}>
                <User size={18} />
              </span>
              <input
                type="text"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                className={inputClass}
                placeholder="Nguyễn Văn A"
                required
              />
            </div>
          </div>

          <div>
            <label className={labelClass}>{t('auth.email')}</label>
            <div className="relative group">
              <span className={iconContainerClass}>
                <Mail size={18} />
              </span>
              <input
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                className={inputClass}
                placeholder="email@example.com"
                required
              />
            </div>
          </div>

          <div>
            <label className={labelClass}>{t('auth.phone')}</label>
            <div className="relative group">
              <span className={iconContainerClass}>
                <Phone size={18} />
              </span>
              <input
                type="tel"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                className={inputClass}
                placeholder="0987 654 321"
                required
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className={labelClass}>{t('auth.password')}</label>
              <div className="relative group">
                <span className={iconContainerClass}>
                  <Lock size={18} />
                </span>
                <input
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  className={inputClass}
                  placeholder="••••••••"
                  required
                />
              </div>
            </div>
            <div>
              <label className={labelClass}>{t('auth.confirm_password')}</label>
              <div className="relative group">
                <span className={iconContainerClass}>
                  <Lock size={18} />
                </span>
                <input
                  type="password"
                  value={formData.password_confirmation}
                  onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
                  className={inputClass}
                  placeholder="••••••••"
                  required
                />
              </div>
            </div>
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full bg-slate-900 hover:bg-blue-600 text-white font-bold py-4 rounded-2xl transition-all shadow-xl shadow-slate-200 hover:shadow-blue-200 mt-4 active:scale-[0.98] transform disabled:bg-slate-300"
          >
            {isLoading ? t('auth.processing') : t('auth.register_button')}
          </button>
        </form>

        <div className="mt-10 text-center text-sm">
          <span className="text-slate-500">{t('auth.have_account')} </span>
          <Link to="/login" className="font-bold text-blue-600 hover:text-blue-700 transition-colors ml-1">
            {t('auth.login_button')}
          </Link>
        </div>
      </div>
    </div>
  );
};

export default RegisterPage;
