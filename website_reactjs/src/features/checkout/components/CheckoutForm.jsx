import React, { useEffect } from 'react';
import { MapPin, CreditCard, Truck, Package, ChevronDown } from 'lucide-react';
import useSettingsStore from '../../../store/useSettingsStore';
import useAddressSelect from '../../address/hooks/useAddressSelect';

const inputClass = "w-full px-5 py-3.5 bg-white/50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all";
const labelClass = "block text-sm font-semibold text-slate-700 mb-2 ml-1";
const sectionClass = "bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-8 md:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.02)]";

const CheckoutForm = ({ formData, onInputChange, onPaymentChange, onShippingChange, onBillingToggle, isSubmitting }) => {
  const t = useSettingsStore(state => state.t);
  const getSetting = useSettingsStore(state => state.getSetting);
  
  const paymentMethods = getSetting('checkout.payment_gateways') || [{ id: 'cod', name: 'Thanh toán khi nhận hàng (COD)', icon: 'truck' }];
  const shippingMethods = getSetting('checkout.shipping_methods') || [];
  
  const { countries, states, regions, subRegions, loading, fetchStates, fetchRegions, fetchSubRegions } = useAddressSelect();

  // Fetch states when country changes
  useEffect(() => {
    if (formData.country) {
      fetchStates(formData.country);
    }
  }, [formData.country, fetchStates]);

  // Fetch regions when state changes
  useEffect(() => {
    if (formData.country && formData.state) {
      fetchRegions(formData.country, formData.state);
    }
  }, [formData.country, formData.state, fetchRegions]);

  // Fetch sub-regions when region changes
  useEffect(() => {
    if (formData.country && formData.state && formData.region) {
      fetchSubRegions(formData.country, formData.state, formData.region);
    }
  }, [formData.country, formData.state, formData.region, fetchSubRegions]);

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
          <div className="md:col-span-1">
            <label className={labelClass}>{t('checkout.city') || 'Thành phố'}</label>
            <input
              type="text"
              name="city"
              required
              value={formData.city}
              onChange={onInputChange}
              className={inputClass}
              placeholder="Hà Nội, TP.HCM..."
            />
          </div>
          <div className="md:col-span-1">
            <label className={labelClass}>{t('checkout.country') || 'Quốc gia'}</label>
            <div className="relative">
              <select
                name="country"
                required
                value={formData.country || 'VN'}
                onChange={onInputChange}
                className={`${inputClass} appearance-none pr-10`}
              >
                <option value="">{t('checkout.select_country') || 'Chọn quốc gia'}</option>
                {Object.entries(countries).map(([code, name]) => (
                  <option key={code} value={code}>{name}</option>
                ))}
              </select>
              <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
            </div>
          </div>
          {formData.country === 'vn' && (
            <>
              <div className="md:col-span-1">
                <label className={labelClass}>{t('checkout.state') || 'Tỉnh/Thành phố'}</label>
                <div className="relative">
                  <select
                    name="state"
                    required
                    value={formData.state || ''}
                    onChange={onInputChange}
                    className={`${inputClass} appearance-none pr-10`}
                  >
                    <option value="">{t('checkout.select_state') || 'Chọn tỉnh/thành phố'}</option>
                    {Object.entries(states).map(([id, name]) => (
                      <option key={id} value={id}>{name}</option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                </div>
              </div>
              {formData.state && (
                <div className="md:col-span-1">
                  <label className={labelClass}>{t('checkout.region') || 'Quận/Huyện'}</label>
                  <div className="relative">
                    <select
                      name="region"
                      value={formData.region || ''}
                      onChange={onInputChange}
                      className={`${inputClass} appearance-none pr-10`}
                    >
                      <option value="">{t('checkout.select_region') || 'Chọn quận/huyện'}</option>
                      {Object.entries(regions).map(([id, name]) => (
                        <option key={id} value={id}>{name}</option>
                      ))}
                    </select>
                    <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                  </div>
                </div>
              )}
              {formData.region && (
                <div className="md:col-span-1">
                  <label className={labelClass}>{t('checkout.sub_region') || 'Phường/Xã'}</label>
                  <div className="relative">
                    <select
                      name="subRegion"
                      value={formData.subRegion || ''}
                      onChange={onInputChange}
                      className={`${inputClass} appearance-none pr-10`}
                    >
                      <option value="">{t('checkout.select_sub_region') || 'Chọn phường/xã'}</option>
                      {Object.entries(subRegions).map(([id, name]) => (
                        <option key={id} value={id}>{name}</option>
                      ))}
                    </select>
                    <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                  </div>
                </div>
              )}
            </>
          )}
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

      {/* Billing Address Toggle */}
      <section className={sectionClass}>
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
            <MapPin size={22} />
          </div>
          <h2 className="text-xl font-bold text-slate-900">{t('checkout.billing_info') || 'Thông tin thanh toán'}</h2>
        </div>
        
        <label className="flex items-center gap-3 p-4 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-slate-300 transition-all mb-6">
          <input 
            type="checkbox"
            name="billingSameAsShipping"
            checked={formData.billingSameAsShipping}
            onChange={onBillingToggle}
            className="w-5 h-5 accent-orange-600"
          />
          <span className="font-medium text-slate-700">{t('checkout.billing_same_as_shipping') || 'Địa chỉ thanh toán giống địa chỉ giao hàng'}</span>
        </label>

        {!formData.billingSameAsShipping && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="md:col-span-1">
              <label className={labelClass}>{t('checkout.first_name')}</label>
              <input
                type="text"
                name="billingFirstName"
                required={!formData.billingSameAsShipping}
                value={formData.billingFirstName}
                onChange={onInputChange}
                className={inputClass}
              />
            </div>
            <div className="md:col-span-1">
              <label className={labelClass}>{t('checkout.last_name')}</label>
              <input
                type="text"
                name="billingLastName"
                required={!formData.billingSameAsShipping}
                value={formData.billingLastName}
                onChange={onInputChange}
                className={inputClass}
              />
            </div>
            <div className="md:col-span-1">
              <label className={labelClass}>{t('checkout.phone')}</label>
              <input
                type="tel"
                name="billingPhone"
                required={!formData.billingSameAsShipping}
                value={formData.billingPhone}
                onChange={onInputChange}
                className={inputClass}
              />
            </div>
            <div className="md:col-span-1">
              <label className={labelClass}>{t('checkout.email')}</label>
              <input
                type="email"
                name="billingEmail"
                required={!formData.billingSameAsShipping}
                value={formData.billingEmail}
                onChange={onInputChange}
                className={inputClass}
              />
            </div>
            <div className="md:col-span-2">
              <label className={labelClass}>{t('checkout.address')}</label>
              <input
                type="text"
                name="billingAddress"
                required={!formData.billingSameAsShipping}
                value={formData.billingAddress}
                onChange={onInputChange}
                className={inputClass}
              />
            </div>
            <div className="md:col-span-1">
              <label className={labelClass}>{t('checkout.city') || 'Thành phố'}</label>
              <input
                type="text"
                name="billingCity"
                required={!formData.billingSameAsShipping}
                value={formData.billingCity}
                onChange={onInputChange}
                className={inputClass}
              />
            </div>
            <div className="md:col-span-1">
              <label className={labelClass}>{t('checkout.country') || 'Quốc gia'}</label>
              <div className="relative">
                <select
                  name="billingCountry"
                  required={!formData.billingSameAsShipping}
                  value={formData.billingCountry || 'VN'}
                  onChange={onInputChange}
                  className={`${inputClass} appearance-none pr-10`}
                >
                  <option value="">{t('checkout.select_country') || 'Chọn quốc gia'}</option>
                  {Object.entries(countries).map(([code, name]) => (
                    <option key={code} value={code}>{name}</option>
                  ))}
                </select>
                <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
              </div>
            </div>
            {formData.billingCountry === 'vn' && (
              <>
                <div className="md:col-span-1">
                  <label className={labelClass}>{t('checkout.state') || 'Tỉnh/Thành phố'}</label>
                  <div className="relative">
                    <select
                      name="billingState"
                      required={!formData.billingSameAsShipping}
                      value={formData.billingState || ''}
                      onChange={onInputChange}
                      className={`${inputClass} appearance-none pr-10`}
                    >
                      <option value="">{t('checkout.select_state') || 'Chọn tỉnh/thành phố'}</option>
                      {Object.entries(states).map(([id, name]) => (
                        <option key={id} value={id}>{name}</option>
                      ))}
                    </select>
                    <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                  </div>
                </div>
                {formData.billingState && (
                  <div className="md:col-span-1">
                    <label className={labelClass}>{t('checkout.region') || 'Quận/Huyện'}</label>
                    <div className="relative">
                      <select
                        name="billingRegion"
                        value={formData.billingRegion || ''}
                        onChange={onInputChange}
                        className={`${inputClass} appearance-none pr-10`}
                      >
                        <option value="">{t('checkout.select_region') || 'Chọn quận/huyện'}</option>
                        {Object.entries(regions).map(([id, name]) => (
                          <option key={id} value={id}>{name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                    </div>
                  </div>
                )}
                {formData.billingRegion && (
                  <div className="md:col-span-1">
                    <label className={labelClass}>{t('checkout.sub_region') || 'Phường/Xã'}</label>
                    <div className="relative">
                      <select
                        name="billingSubRegion"
                        value={formData.billingSubRegion || ''}
                        onChange={onInputChange}
                        className={`${inputClass} appearance-none pr-10`}
                      >
                        <option value="">{t('checkout.select_sub_region') || 'Chọn phường/xã'}</option>
                        {Object.entries(subRegions).map(([id, name]) => (
                          <option key={id} value={id}>{name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                    </div>
                  </div>
                )}
              </>
            )}
          </div>
        )}
      </section>

      <section className={sectionClass}>
        <div className="flex items-center gap-3 mb-8">
          <div className="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
            <CreditCard size={22} />
          </div>
          <h2 className="text-xl font-bold text-slate-900">{t('checkout.payment_method')}</h2>
        </div>
        
        <div className="space-y-4">
          {paymentMethods.map((method) => (
            <label key={method.id} className={`flex items-center gap-4 p-5 border-2 rounded-2xl cursor-pointer transition-all ${
              String(formData.paymentMethod) === String(method.id) ? 'border-blue-600 bg-blue-50/30' : 'border-slate-200 hover:border-slate-300'
            }`}>
              <input 
                type="radio" 
                name="paymentMethod" 
                value={method.id}
                checked={String(formData.paymentMethod) === String(method.id)}
                onChange={onPaymentChange}
                className="w-5 h-5 accent-blue-600" 
              />
              <div className="flex-1">
                <p className="font-bold text-slate-900">{method.name}</p>
              </div>
              {method.icon === 'truck' && <Truck className="text-blue-600" />}
              {method.icon === 'credit-card' && <CreditCard className="text-blue-600" />}
              {method.icon === 'paypal' && <span className="text-blue-600 font-bold">PayPal</span>}
              {method.icon === 'wallet' && <span className="text-pink-600 font-bold">MoMo</span>}
            </label>
          ))}
        </div>
      </section>

      {shippingMethods.length > 0 && (
        <section className={sectionClass}>
          <div className="flex items-center gap-3 mb-8">
            <div className="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
              <Package size={22} />
            </div>
            <h2 className="text-xl font-bold text-slate-900">Phương thức vận chuyển</h2>
          </div>
          
          <div className="space-y-4">
            {shippingMethods.map((method) => (
              <label key={method.id} className={`flex items-center gap-4 p-5 border-2 rounded-2xl cursor-pointer transition-all ${
                String(formData.shippingMethod) === String(method.id) ? 'border-green-600 bg-green-50/30' : 'border-slate-200 hover:border-slate-300'
              }`}>
                <input 
                  type="radio" 
                  name="shippingMethod" 
                  value={method.id}
                  checked={String(formData.shippingMethod) === String(method.id)}
                  onChange={onShippingChange}
                  className="w-5 h-5 accent-green-600" 
                />
                <div className="flex-1">
                  <p className="font-bold text-slate-900">{method.name}</p>
                  {method.settings?.cost && (
                    <p className="text-sm text-green-600 font-medium">{method.settings.cost.toLocaleString('vi-VN')}₫</p>
                  )}
                </div>
              </label>
            ))}
          </div>
        </section>
      )}
    </div>
  );
};

export default CheckoutForm;
