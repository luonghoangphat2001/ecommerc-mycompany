import React from 'react';
import { Link } from 'react-router-dom';
import { Lock, Mail } from 'lucide-react';

import useSettingsStore from '../../store/useSettingsStore';
import useLoginForm from '../../features/auth/hooks/useLoginForm';

const inputClass = "w-full pl-12 pr-4 py-3.5 bg-white/50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-400";
const labelClass = "block text-sm font-semibold text-slate-700 mb-2 ml-1";
const iconContainerClass = "absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors";

const LoginPage = () => {
  const t = useSettingsStore((state) => state.t);
  const {
    email,
    setEmail,
    password,
    setPassword,
    error,
    handleLogin
  } = useLoginForm();

  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden font-sans">
      <div className="absolute w-[600px] h-[600px] bg-blue-300 rounded-full blur-[120px] opacity-10 -top-20 -left-20 pointer-events-none"></div>
      <div className="absolute w-[500px] h-[500px] bg-purple-300 rounded-full blur-[120px] opacity-10 -bottom-20 -right-20 pointer-events-none"></div>

      <div className="bg-white/60 backdrop-blur-xl p-8 md:p-12 w-full max-w-[450px] border border-white/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[2.5rem] relative z-10 mx-4">
        <div className="text-center mb-10">
          <h1 className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3 tracking-tight">
            NovaStore
          </h1>
          <p className="text-slate-500 font-medium">{t('auth.login_subtitle')}</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100 font-medium flex items-center animate-shake">
            {error}
          </div>
        )}

        <form onSubmit={handleLogin} className="space-y-6">
          <div>
            <label className={labelClass}>{t('auth.email')}</label>
            <div className="relative group">
              <span className={iconContainerClass}>
                <Mail size={18} />
              </span>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className={inputClass}
                placeholder="email@example.com"
                required
              />
            </div>
          </div>

          <div>
            <div className="flex items-center justify-between mb-2 ml-1">
              <label className="block text-sm font-semibold text-slate-700">{t('auth.password')}</label>
              <Link to="/forgot-password" size="sm" className="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                {t('auth.forgot_password')}
              </Link>
            </div>
            <div className="relative group">
              <span className={iconContainerClass}>
                <Lock size={18} />
              </span>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className={inputClass}
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <button
            type="submit"
            className="w-full bg-slate-900 hover:bg-blue-600 text-white font-bold py-4 rounded-2xl transition-all shadow-xl shadow-slate-200 hover:shadow-blue-200 mt-4 active:scale-[0.98] transform"
          >
            {t('auth.login_button')}
          </button>
        </form>

        <div className="mt-10 text-center text-sm">
          <span className="text-slate-500">{t('auth.no_account')} </span>
          <Link to="/register" className="font-bold text-blue-600 hover:text-blue-700 transition-colors ml-1">
            {t('auth.register_button')}
          </Link>
        </div>

      </div>
    </div>
  );
};

export default LoginPage;
