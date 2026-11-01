import useSettingsStore from '../store/useSettingsStore';

const resolveLocale = (localeOrFormat) => {
  if (localeOrFormat && localeOrFormat !== 'short' && localeOrFormat !== 'long' && /^[a-z]{2}(-[A-Z]{2})?$/.test(localeOrFormat)) {
    return localeOrFormat === 'en' ? 'en-US' : localeOrFormat === 'vi' ? 'vi-VN' : localeOrFormat;
  }
  const activeLang = useSettingsStore?.getState?.()?.language || 'vi';
  return activeLang === 'en' ? 'en-US' : 'vi-VN';
};

/**
 * Format date according to active locale
 * @param {string|Date} dateString - Date string or Date object
 * @param {string} [localeOrFormat] - Locale ('vi-VN', 'en-US') or format hint ('short', 'long')
 * @returns {string} Formatted date
 */
export const formatDate = (dateString, localeOrFormat) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '';

  const locale = resolveLocale(localeOrFormat);
  const options =
    localeOrFormat === 'short'
      ? { day: '2-digit', month: 'short', year: 'numeric' }
      : { day: '2-digit', month: '2-digit', year: 'numeric' };

  try {
    return date.toLocaleDateString(locale, options);
  } catch (e) {
    return date.toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  }
};

/**
 * Format order date for display
 * @param {string|Date} dateString - Date string or Date object
 * @param {string} [localeOrFormat] - Locale (default: active locale)
 * @returns {string} Formatted date
 */
export const formatOrderDate = (dateString, localeOrFormat) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '';

  const locale = resolveLocale(localeOrFormat);
  try {
    return date.toLocaleDateString(locale, {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch (e) {
    return date.toLocaleDateString('vi-VN');
  }
};

/**
 * Format date for input fields (YYYY-MM-DD)
 * @param {string|Date} dateString - Date string or Date object
 * @returns {string} Formatted date string
 */
export const formatDateForInput = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '';
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

/**
 * Format relative time (e.g., "2 days ago")
 * @param {string|Date} dateString - Date string or Date object
 * @param {string} [localeOrFormat] - Locale
 * @returns {string} Relative time string
 */
export const formatRelativeTime = (dateString, localeOrFormat) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '';

  const now = new Date();
  const diffMs = now - date;
  const diffSecs = Math.floor(diffMs / 1000);
  const diffMins = Math.floor(diffSecs / 60);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);

  const locale = resolveLocale(localeOrFormat);
  try {
    const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
    if (diffSecs < 60) return rtf.format(-diffSecs, 'second');
    if (diffMins < 60) return rtf.format(-diffMins, 'minute');
    if (diffHours < 24) return rtf.format(-diffHours, 'hour');
    if (diffDays < 30) return rtf.format(-diffDays, 'day');
  } catch (e) {
    // ignore
  }

  return formatDate(dateString, locale);
};

export const isPastDate = (dateString) => {
  if (!dateString) return false;
  const date = new Date(dateString);
  return date < new Date();
};

export const isFutureDate = (dateString) => {
  if (!dateString) return false;
  const date = new Date(dateString);
  return date > new Date();
};

export const daysBetween = (date1, date2) => {
  const d1 = new Date(date1);
  const d2 = new Date(date2);
  const diffTime = Math.abs(d2 - d1);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};
