/**
 * Date formatting utilities
 */

/**
 * Format date to Vietnamese locale
 * @param {string|Date} dateString - Date string or Date object
 * @param {string} locale - Locale (default: vi-VN)
 * @returns {string} Formatted date
 */
export const formatDate = (dateString, locale = 'vi-VN') => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(locale, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

/**
 * Format order date for display
 * @param {string|Date} dateString - Date string or Date object
 * @param {string} locale - Locale (default: vi-VN)
 * @returns {string} Formatted date
 */

export const formatOrderDate = (dateString, locale = 'vi-VN') => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(locale, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

/**
 * Format date for input fields (YYYY-MM-DD)
 * @param {string|Date} dateString - Date string or Date object
 * @returns {string} Formatted date string
 */
export const formatDateForInput = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

/**
 * Format relative time (e.g., "2 days ago")
 * @param {string|Date} dateString - Date string or Date object
 * @param {string} locale - Locale (default: vi-VN)
 * @returns {string} Relative time string
 */
export const formatRelativeTime = (dateString, locale = 'vi-VN') => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffSecs = Math.floor(diffMs / 1000);
  const diffMins = Math.floor(diffSecs / 60);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);

  const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });

  if (diffSecs < 60) return rtf.format(-diffSecs, 'second');
  if (diffMins < 60) return rtf.format(-diffMins, 'minute');
  if (diffHours < 24) return rtf.format(-diffHours, 'hour');
  if (diffDays < 30) return rtf.format(-diffDays, 'day');
  
  return date.toLocaleDateString(locale);
};

/**
 * Check if date is in the past
 * @param {string|Date} dateString - Date string or Date object
 * @returns {boolean} True if date is in the past
 */
export const isPastDate = (dateString) => {
  if (!dateString) return false;
  const date = new Date(dateString);
  return date < new Date();
};

/**
 * Check if date is in the future
 * @param {string|Date} dateString - Date string or Date object
 * @returns {boolean} True if date is in the future
 */
export const isFutureDate = (dateString) => {
  if (!dateString) return false;
  const date = new Date(dateString);
  return date > new Date();
};

/**
 * Get date difference in days
 * @param {string|Date} date1 - First date
 * @param {string|Date} date2 - Second date
 * @returns {number} Difference in days
 */
export const daysBetween = (date1, date2) => {
  const d1 = new Date(date1);
  const d2 = new Date(date2);
  const diffTime = Math.abs(d2 - d1);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};
