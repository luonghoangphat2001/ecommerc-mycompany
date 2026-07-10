import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';

const ShippingForm = ({ form, shippingMethods, selectedShippingMethod, onShippingMethodChange }) => {
  const { translate } = useTranslation('checkout');
  const { register, formState: { errors } } = form;

  const containerClass = "space-y-6";
  const sectionClass = "space-y-4";
  const titleClass = "text-lg font-semibold";
  const gridClass = "grid grid-cols-1 md:grid-cols-2 gap-4";
  const inputClass = "w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent";
  const errorClass = "text-red-500 text-sm";
  const radioContainerClass = "space-y-2";
  const radioClass = "flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50";
  const selectedRadioClass = "border-blue-500 bg-blue-50";

  return (
    <div className={containerClass}>
      <div className={sectionClass}>
        <h2 className={titleClass}>{translate('shipping_info')}</h2>
        <div className={gridClass}>
          <div>
            <label className="block text-sm font-medium mb-1">{translate('profile.first_name')}</label>
            <input {...register('shipping_address.first_name')} className={inputClass} />
            {errors.shipping_address?.first_name && <p className={errorClass}>{errors.shipping_address.first_name.message}</p>}
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">{translate('profile.last_name')}</label>
            <input {...register('shipping_address.last_name')} className={inputClass} />
            {errors.shipping_address?.last_name && <p className={errorClass}>{errors.shipping_address.last_name.message}</p>}
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">{translate('profile.email')}</label>
            <input {...register('shipping_address.email')} type="email" className={inputClass} />
            {errors.shipping_address?.email && <p className={errorClass}>{errors.shipping_address.email.message}</p>}
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">{translate('profile.phone')}</label>
            <input {...register('shipping_address.phone')} className={inputClass} />
            {errors.shipping_address?.phone && <p className={errorClass}>{errors.shipping_address.phone.message}</p>}
          </div>
          <div className="md:col-span-2">
            <label className="block text-sm font-medium mb-1">{translate('address.street')}</label>
            <input {...register('shipping_address.address')} className={inputClass} />
            {errors.shipping_address?.address && <p className={errorClass}>{errors.shipping_address.address.message}</p>}
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">{translate('address.city')}</label>
            <input {...register('shipping_address.city')} className={inputClass} />
            {errors.shipping_address?.city && <p className={errorClass}>{errors.shipping_address.city.message}</p>}
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">{translate('address.postal_code')}</label>
            <input {...register('shipping_address.postal_code')} className={inputClass} />
            {errors.shipping_address?.postal_code && <p className={errorClass}>{errors.shipping_address.postal_code.message}</p>}
          </div>
        </div>
      </div>

      <div className={sectionClass}>
        <h2 className={titleClass}>{translate('payment_method')}</h2>
        <div className={radioContainerClass}>
          {shippingMethods.map((method) => (
            <label
              key={method.id}
              className={`${radioClass} ${selectedShippingMethod === method.id ? selectedRadioClass : ''}`}
            >
              <input
                type="radio"
                value={method.id}
                {...register('shipping_method_id')}
                onChange={() => onShippingMethodChange(method.id)}
              />
              <div>
                <span className="font-medium">{method.name}</span>
                <span className="text-sm text-gray-600 ml-2">{method.price}</span>
              </div>
            </label>
          ))}
        </div>
        {errors.shipping_method_id && <p className={errorClass}>{errors.shipping_method_id.message}</p>}
      </div>
    </div>
  );
};

export default ShippingForm;
