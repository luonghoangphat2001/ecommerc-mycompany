// Formatters - Centralized formatting logic
// No inline formatting in components

/**
 * Format currency using API settings
 * @param {number} amount - Amount to format
 * @param {string} currencyCode - Currency code (VND, USD, etc.) from API
 * @param {string} symbol - Currency symbol from API
 * @returns {string} Formatted currency
 */
export const formatCurrency = (amount, currencyCode = 'VND', symbol = '₫') => {
  if (amount === undefined || amount === null || isNaN(amount)) return '';
  
  // Determine locale based on currency
  const locale = currencyCode === 'VND' ? 'vi-VN' : 
                 currencyCode === 'USD' ? 'en-US' : 'vi-VN';
  
  const formatted = Number(amount).toLocaleString(locale, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  });
  
  return symbol ? `${formatted}${symbol}` : formatted;
};

/**
 * Format date to Vietnamese locale
 * @param {string|Date} date - Date to format
 * @param {object} options - Intl.DateTimeFormat options
 * @returns {string} Formatted date
 */
export const formatDate = (date, options = {}) => {
  if (!date) return '';
  const defaultOptions = {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    ...options
  };
  return new Date(date).toLocaleDateString('vi-VN', defaultOptions);
};

/**
 * Format phone number to Vietnamese format
 * @param {string} phone - Phone number
 * @returns {string} Formatted phone
 */
export const formatPhone = (phone) => {
  if (!phone) return '';
  // Remove non-digits
  const cleaned = phone.replace(/\D/g, '');
  if (cleaned.startsWith('84')) {
    return `+84-${cleaned.slice(2, 5)}-${cleaned.slice(5, 8)}-${cleaned.slice(8, 12)}`;
  }
  if (cleaned.startsWith('0')) {
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
    address.city,
    address.country
  ].filter(Boolean);
  return parts.join(', ');
};

/**
 * Format order status to display badge
 * @param {string} status - Order status
 * @returns {object} { label, colorClass }
 */
export const formatOrderStatus = (status) => {
  const statusMap = {
    'pending': { label: 'Chờ xử lý', color: 'bg-yellow-100 text-yellow-700' },
    'processing': { label: 'Đang xử lý', color: 'bg-blue-100 text-blue-700' },
    'shipped': { label: 'Đang giao', color: 'bg-indigo-100 text-indigo-700' },
    'completed': { label: 'Hoàn thành', color: 'bg-green-100 text-green-700' },
    'cancelled': { label: 'Đã hủy', color: 'bg-red-100 text-red-700' },
  };
  return statusMap[status?.toLowerCase()] || { label: status, color: 'bg-gray-100 text-gray-700' };
};

/**
 * Truncate text with ellipsis
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Max length
 * @returns {string} Truncated text
 */
export const truncateText = (text, maxLength = 50) => {
  if (!text || text.length <= maxLength) return text;
  return text.slice(0, maxLength).trim() + '...';
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
