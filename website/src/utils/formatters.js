import useSettingsStore from '../store/useSettingsStore';

const getBrowserLocale = (lang) => {
  const activeLang = lang || useSettingsStore?.getState?.()?.language || 'vi';
  return activeLang === 'en' ? 'en-US' : 'vi-VN';
};

/**
 * Safely extract localized string from string or { vi, en } translation object
 * @param {string|object} value - String or translation dictionary { vi, en }
 * @param {string} [lang] - Optional language override ('vi' | 'en')
 * @returns {string}
 */
export const getLocalizedText = (value, lang) => {
  if (value === null || value === undefined) return '';
  if (typeof value === 'string' || typeof value === 'number') return String(value);
  if (typeof value === 'object') {
    const activeLang = lang || useSettingsStore?.getState?.()?.language || 'vi';
    const picked = value[activeLang] ?? value.vi ?? value.en ?? '';
    return typeof picked === 'string' || typeof picked === 'number' ? String(picked) : '';
  }
  return '';
};

/**
 * Format currency using API settings and active locale
 * @param {number} amount - Amount to format
 * @param {string} currencyCode - Currency code (VND, USD, etc.) from API
 * @param {string} symbol - Currency symbol from API
 * @returns {string} Formatted currency
 */
export const formatCurrency = (amount, currencyCode = 'VND', symbol = '₫') => {
  if (amount === undefined || amount === null || isNaN(amount)) return '';

  const resolvedCode =
    typeof currencyCode === 'object' ? currencyCode?.code || 'VND' : currencyCode || 'VND';
  const resolvedSymbol =
    typeof currencyCode === 'object' ? currencyCode?.symbol || '₫' : symbol ?? '₫';

  const activeLang = useSettingsStore?.getState?.()?.language || 'vi';
  const locale =
    resolvedCode === 'USD'
      ? 'en-US'
      : resolvedCode === 'VND'
      ? activeLang === 'en'
        ? 'en-US'
        : 'vi-VN'
      : getBrowserLocale(activeLang);

  const formatted = Number(amount).toLocaleString(locale, {
    minimumFractionDigits: 0,
    maximumFractionDigits: resolvedCode === 'VND' ? 0 : 2,
  });

  if (!resolvedSymbol) return formatted;
  return resolvedCode === 'USD' ? `${resolvedSymbol}${formatted}` : `${formatted}${resolvedSymbol}`;
};

/**
 * Format date according to active locale
 * @param {string|Date} date - Date to format
 * @param {object} options - Intl.DateTimeFormat options
 * @returns {string} Formatted date
 */
export const formatDate = (date, options = {}) => {
  if (!date) return '';
  const d = new Date(date);
  if (isNaN(d.getTime())) return '';

  const locale = getBrowserLocale();
  const resolvedOptions =
    typeof options === 'object' && options !== null
      ? {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          ...options,
        }
      : {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
        };

  return d.toLocaleDateString(locale, resolvedOptions);
};

/**
 * Format phone number
 * @param {string} phone - Phone number
 * @returns {string} Formatted phone
 */
export const formatPhone = (phone) => {
  if (!phone) return '';
  const cleaned = String(phone).replace(/\D/g, '');
  if (cleaned.startsWith('84')) {
    return `+84-${cleaned.slice(2, 5)}-${cleaned.slice(5, 8)}-${cleaned.slice(8, 12)}`;
  }
  if (cleaned.startsWith('0') && cleaned.length >= 10) {
    return `${cleaned.slice(0, 4)}-${cleaned.slice(4, 7)}-${cleaned.slice(7, 10)}`;
  }
  return phone;
};

/**
 * Format address as single line
 * @param {object} address - Address object
 * @returns {string} Formatted address
 */
export const formatAddressLine = (address) => {
  if (!address) return '';
  const parts = [
    address.street,
    address.ward,
    address.district,
    address.state,
    address.city,
    address.country,
  ].filter(Boolean);
  return parts.join(', ');
};

/**
 * Format order status to display badge with localized label
 * @param {string} status - Order status
 * @returns {object} { label, color }
 */
export const formatOrderStatus = (status) => {
  const key = status?.toLowerCase();
  const translate = useSettingsStore?.getState?.()?.translate;
  const translatedLabel = translate ? translate(`order.${key}`) : '';

  const colorMap = {
    pending: 'bg-yellow-100 text-yellow-700',
    new: 'bg-sky-100 text-sky-700',
    processing: 'bg-blue-100 text-blue-700',
    shipping: 'bg-indigo-100 text-indigo-700',
    delivering: 'bg-indigo-100 text-indigo-700',
    shipped: 'bg-indigo-100 text-indigo-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
    refunded: 'bg-slate-100 text-slate-700',
  };

  return {
    label: translatedLabel || status || '',
    color: colorMap[key] || 'bg-gray-100 text-gray-700',
  };
};

/**
 * Truncate text with ellipsis
 * @param {string|object} text - Text to truncate
 * @param {number} maxLength - Max length
 * @returns {string} Truncated text
 */
export const truncateText = (text, maxLength = 50) => {
  const str = getLocalizedText(text);
  if (!str) return '';
  const stripped = str.replace(/<[^>]*>/g, '');
  if (stripped.length <= maxLength) return stripped;
  return stripped.slice(0, maxLength).trim() + '...';
};

/**
 * Format product price range
 * @param {number} min - Min price
 * @param {number} max - Max price
 * @returns {string} Formatted range
 */
export const formatPriceRange = (min, max) => {
  if (min === max || !max) return formatCurrency(min);
  return `${formatCurrency(min)} - ${formatCurrency(max)}`;
};
