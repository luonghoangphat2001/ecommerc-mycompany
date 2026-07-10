import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';

const PaymentMethodList = ({ paymentMethods, selectedMethod, onMethodChange }) => {
  const { translate } = useTranslation('checkout');

  const containerClass = "space-y-3";
  const methodClass = "flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition";
  const selectedClass = "border-blue-500 bg-blue-50";
  const iconClass = "w-8 h-8";
  const infoClass = "flex-1";
  const nameClass = "font-medium";
  const descClass = "text-sm text-gray-600";

  const methodIcons = {
    cod: '💵',
    momo: '📱',
    vnpay: '🔷',
    card: '💳',
  };

  return (
    <div className={containerClass}>
      {paymentMethods.map((method) => (
        <div
          key={method.id}
          className={`${methodClass} ${selectedMethod === method.id ? selectedClass : ''}`}
          onClick={() => onMethodChange(method.id)}
        >
          <span className={iconClass}>{methodIcons[method.code] || '💳'}</span>
          <div className={infoClass}>
            <div className={nameClass}>{translate(method.code)}</div>
            {method.description && <div className={descClass}>{method.description}</div>}
          </div>
          <div className="w-5 h-5 border-2 rounded-full flex items-center justify-center">
            {selectedMethod === method.id && <div className="w-3 h-3 bg-blue-500 rounded-full" />}
          </div>
        </div>
      ))}
    </div>
  );
};

export default PaymentMethodList;
