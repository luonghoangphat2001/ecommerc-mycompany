import React from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { formatOrderDate } from '../../../utils/date';
import { formatCurrency } from '../../../utils/formatters';

const ThankYouPage = ({ order }) => {
  const { translate } = useTranslation('checkout');
  const navigate = useNavigate();

  const containerClass = "max-w-2xl mx-auto p-8 text-center";
  const iconClass = "w-20 h-20 mx-auto mb-6 text-green-500";
  const titleClass = "text-3xl font-bold text-gray-900 mb-4";
  const messageClass = "text-gray-600 mb-8";
  const orderInfoClass = "bg-gray-50 rounded-lg p-6 mb-8 text-left";
  const infoRowClass = "flex justify-between py-2 border-b last:border-0";
  const labelClass = "text-gray-600";
  const valueClass = "font-medium";
  const buttonClass = "bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition";

  if (!order) return null;

  return (
    <div className={containerClass}>
      <svg className={iconClass} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>

      <h1 className={titleClass}>{translate('order_success')}</h1>
      <p className={messageClass}>{translate('order_success_message')}</p>

      <div className={orderInfoClass}>
        <div className={infoRowClass}>
          <span className={labelClass}>{translate('order.order_id')}</span>
          <span className={valueClass}>#{order.id}</span>
        </div>
        <div className={infoRowClass}>
          <span className={labelClass}>{translate('order.date')}</span>
          <span className={valueClass}>{formatOrderDate(order.created_at)}</span>
        </div>
        <div className={infoRowClass}>
          <span className={labelClass}>{translate('order.status')}</span>
          <span className={valueClass}>{translate(`order.${order.status}`)}</span>
        </div>
        <div className={infoRowClass}>
          <span className={labelClass}>{translate('order.total')}</span>
          <span className={valueClass}>{formatCurrency(order.total)}</span>
        </div>
      </div>

      <button onClick={() => navigate('/')} className={buttonClass}>
        {translate('continue_shopping')}
      </button>
    </div>
  );
};

export default ThankYouPage;
