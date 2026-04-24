import React from 'react';
import { Lock } from 'lucide-react';
import useSettingsStore from '../../store/useSettingsStore';
import useResetPassword from '../../features/auth/hooks/useResetPassword';

const ResetPasswordPage = () => {
  const t = useSettingsStore(state => state.t);
  const {
    formData,
    setFormData,
    status,
    error,
    isLoading,
    handleSubmit
  } = useResetPassword();


  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden font-sans">
      <div className="bg-white/60 backdrop-blur-xl p-8 md:p-12 w-full max-w-[450px] border border-white/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[2.5rem] relative z-10 mx-4">
        <div className="text-center mb-10">
          <h1 className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3 tracking-tight">
            {t('auth.reset_password_title')}
          </h1>
          <p className="text-slate-500 font-medium">{t('auth.reset_password_subtitle')}</p>
        </div>

        {status && (
          <div className="mb-6 p-4 bg-green-50 text-green-600 text-sm rounded-2xl border border-green-100 font-medium">
            {status}
          </div>
        )}

        {error && (
          <div className="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100 font-medium">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label className="block text-sm font-semibold text-slate-700 mb-2 ml-1">{t('account.new_password')}</label>
            <div className="relative group">
              <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                <Lock size={18} />
              </span>
              <input
                type="password"
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                className="w-full pl-12 pr-4 py-3.5 bg-white/50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-400"
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-semibold text-slate-700 mb-2 ml-1">{t('auth.confirm_password')}</label>
            <div className="relative group">
              <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                <Lock size={18} />
              </span>
              <input
                type="password"
                value={formData.password_confirmation}
                onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
                className="w-full pl-12 pr-4 py-3.5 bg-white/50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-400"
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full bg-slate-900 hover:bg-blue-600 text-white font-bold py-4 rounded-2xl transition-all shadow-xl shadow-slate-200 hover:shadow-blue-200 mt-4 active:scale-[0.98] transform disabled:bg-slate-300"
          >
            {isLoading ? t('auth.processing') : t('auth.update_password_button')}
          </button>
        </form>
      </div>
    </div>
  );
};

export default ResetPasswordPage;
