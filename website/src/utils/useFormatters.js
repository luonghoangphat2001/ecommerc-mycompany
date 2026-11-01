import { useCallback } from 'react';
import useSettingsStore from '../store/useSettingsStore';
import {
  formatCurrency as formatCurrencyBase,
  formatDate as formatDateBase,
  formatPhone,
  formatOrderStatus as formatOrderStatusBase,
  getLocalizedText as getLocalizedTextBase,
} from './formatters';

/**
 * Hook to use formatters with API currency and language settings
 * Returns format functions that automatically react to language & currency changes
 */
export const useFormatters = () => {
  const currency = useSettingsStore((state) => state.currency);
  const language = useSettingsStore((state) => state.language);
  const code = typeof currency === 'object' ? currency?.code || 'VND' : currency || 'VND';
  const symbol = typeof currency === 'object' ? currency?.symbol || '₫' : '₫';

  const formatCurrency = useCallback(
    (amount) => {
      return formatCurrencyBase(amount, code, symbol);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [code, symbol, language]
  );

  const formatPriceRange = useCallback(
    (min, max) => {
      if (min === max || !max) return formatCurrencyBase(min, code, symbol);
      return `${formatCurrencyBase(min, code, symbol)} - ${formatCurrencyBase(max, code, symbol)}`;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [code, symbol, language]
  );

  const formatDate = useCallback(
    (date, options) => {
      return formatDateBase(date, options);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [language]
  );

  const formatOrderStatus = useCallback(
    (status) => {
      return formatOrderStatusBase(status);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [language]
  );

  const getLocalizedText = useCallback(
    (value) => {
      return getLocalizedTextBase(value, language);
    },
    [language]
  );

  return {
    formatCurrency,
    formatDate,
    formatPhone,
    formatOrderStatus,
    formatPriceRange,
    getLocalizedText,
    currencyCode: code,
    currencySymbol: symbol,
    language,
  };
};

export default useFormatters;
