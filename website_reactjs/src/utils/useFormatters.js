import { useCallback } from 'react';
import useSettingsStore from '../store/useSettingsStore';
import { formatCurrency as formatCurrencyBase, formatDate, formatPhone, formatOrderStatus } from './formatters';

/**
 * Hook to use formatters with API currency settings
 * Returns format functions that automatically use currency from API
 */
export const useFormatters = () => {
  const { code, symbol } = useSettingsStore((state) => state.currency);

  const formatCurrency = useCallback((amount) => {
    return formatCurrencyBase(amount, code, symbol);
  }, [code, symbol]);

  const formatPriceRange = useCallback((min, max) => {
    if (min === max || !max) return formatCurrencyBase(min, code, symbol);
    return `${formatCurrencyBase(min, code, symbol)} - ${formatCurrencyBase(max, code, symbol)}`;
  }, [code, symbol]);

  return {
    formatCurrency,
    formatDate,
    formatPhone,
    formatOrderStatus,
    formatPriceRange,
    currencyCode: code,
    currencySymbol: symbol
  };
};

export default useFormatters;
