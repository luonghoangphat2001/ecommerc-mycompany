/**
 * Format currency using Intl.NumberFormat
 * @param {number} amount 
 * @param {string} currencyCode 
 * @param {string} symbol 
 */
export const formatCurrency = (amount, currencyCode = 'VND', symbol = '₫') => {
  if (amount === undefined || amount === null) return '0' + symbol;
  
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: currencyCode,
  }).format(amount).replace(currencyCode, symbol);
};

/**
 * Format date to string
 */
export const formatDate = (date, locale = 'vi-VN') => {
  return new Intl.DateTimeFormat(locale).format(new Date(date));
};
