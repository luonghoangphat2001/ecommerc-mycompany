import React from 'react';
import { MapPin, CreditCard, Truck } from 'lucide-react';
import useSettingsStore from '../../../store/useSettingsStore';

const inputClass = "w-full px-5 py-3.5 bg-white/50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all";
const labelClass = "block text-sm font-semibold text-slate-700 mb-2 ml-1";
const sectionClass = "bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-8 md:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.02)]";

const CheckoutForm = ({ formData, onInputChange, isSubmitting }) => {
  const t = useSettingsStore(state => state.t);

  return (
    <div className="lg:col-span-2 space-y-8">
      <section className={sectionClass}>
        <div className="flex items-center gap-3 mb-8">
          <div className="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
            <MapPin size={22} />
          </div>
          <h2 className="text-xl font-bold text-slate-900">{t('checkout.shipping_info')}</h2>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="md:col-span-1">
            <label className={labelClass}>{t('checkout.first_name')}</label>
            <input
              type="text"
              name="firstName"
              required
              value={formData.firstName}
              onChange={onInputChange}
              className={inputClass}
            />
          </div>
          <div className="md:col-span-1">
            <label className={labelClass}>{t('checkout.last_name')}</label>
            <input
              type="text"
              name="lastName"
              required
              value={formData.lastName}
              onChange={onInputChange}
              className={inputClass}
            />
          </div>
          <div className="md:col-span-1">
            <label className={labelClass}>{t('checkout.email')}</label>
            <input
              type="email"
              name="email"
              required
              value={formData.email}
              onChange={onInputChange}
              className={inputClass}
            />
          </div>
          <div className="md:col-span-1">
            <label className={labelClass}>{t('checkout.phone')}</label>
            <input
              type="tel"
              name="phone"
              required
              value={formData.phone}
              onChange={onInputChange}
              className={inputClass}
            />
          </div>
          <div className="md:col-span-2">
            <label className={labelClass}>{t('checkout.address')}</label>
            <input
              type="text"
              name="address"
              required
              value={formData.address}
              onChange={onInputChange}
              className={inputClass}
              placeholder={t('checkout.address_placeholder')}
            />
          </div>
          <div className="md:col-span-2">
            <label className={labelClass}>{t('checkout.note')}</label>
            <textarea
              rows="3"
              name="note"
              value={formData.note}
              onChange={onInputChange}
              className={`${inputClass} resize-none`}
            />
          </div>
        </div>
      </section>

      <section className={sectionClass}>
        <div className="flex items-center gap-3 mb-8">
          <div className="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
            <CreditCard size={22} />
          </div>
          <h2 className="text-xl font-bold text-slate-900">{t('checkout.payment_method')}</h2>
        </div>
        
        <div className="space-y-4">
          <label className="flex items-center gap-4 p-5 border-2 border-blue-600 bg-blue-50/30 rounded-2xl cursor-pointer transition-all">
            <input type="radio" name="payment" defaultChecked className="w-5 h-5 accent-blue-600" />
            <div className="flex-1">
              <p className="font-bold text-slate-900">{t('checkout.cod')}</p>
              <p className="text-xs text-slate-500">{t('checkout.cod_desc')}</p>
            </div>
            <Truck className="text-blue-600" />
          </label>
        </div>
      </section>
    </div>
  );
};

export default CheckoutForm;
