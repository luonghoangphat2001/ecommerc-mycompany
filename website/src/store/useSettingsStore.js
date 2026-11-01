import { create } from 'zustand';
import axiosClient from '../api/axiosClient';
import { unwrapApiObject } from '../api/apiResponse';
import useMenuStore from './useMenuStore';
import viLocales from '../locales/vi.json';
import enLocales from '../locales/en.json';

const isObject = (item) => item && typeof item === 'object' && !Array.isArray(item);

const deepMerge = (target, source) => {
  const output = { ...target };
  if (isObject(target) && isObject(source)) {
    Object.keys(source).forEach((key) => {
      if (isObject(source[key])) {
        if (!(key in target)) {
          output[key] = source[key];
        } else {
          output[key] = deepMerge(target[key], source[key]);
        }
      } else if (source[key] !== undefined && source[key] !== null && source[key] !== '') {
        output[key] = source[key];
      }
    });
  }
  return output;
};

const getInitialLanguage = () => {
  try {
    const saved = localStorage.getItem('app_language');
    if (saved === 'vi' || saved === 'en') {
      if (typeof document !== 'undefined') {
        document.documentElement.lang = saved;
      }
      return saved;
    }
  } catch (e) {
    // ignore storage errors
  }
  if (typeof document !== 'undefined') {
    document.documentElement.lang = 'vi';
  }
  return 'vi';
};

const resolveKey = (dict, key) => {
  if (!dict || !key) return undefined;
  if (typeof dict[key] === 'string') return dict[key];
  const keys = key.split('.');
  let result = dict;
  for (const keyPart of keys) {
    if (result && typeof result === 'object' && keyPart in result) {
      result = result[keyPart];
    } else {
      return undefined;
    }
  }
  return typeof result === 'string' ? result : undefined;
};

const interpolate = (str, params = {}) => {
  if (typeof str !== 'string' || !params || typeof params !== 'object') return str;
  let output = str;
  Object.entries(params).forEach(([k, v]) => {
    const val = v !== undefined && v !== null ? String(v) : '';
    output = output.replace(new RegExp(`\\{${k}\\}`, 'g'), val);
    output = output.replace(new RegExp(`:${k}\\b`, 'g'), val);
  });
  return output;
};

const CURRENCY_SYMBOLS = {
  VND: '₫',
  USD: '$',
  EUR: '€',
};

const useSettingsStore = create((set, get) => ({
  settings: null,
  loading: false,
  language: getInitialLanguage(),
  currency: {
    code: 'VND',
    symbol: '₫',
  },
  translations: {
    vi: viLocales,
    en: enLocales,
  },

  fetchSettings: async (requestedLang) => {
    set({ loading: true });
    try {
      const activeLang = requestedLang || get().language || getInitialLanguage();
      const response = await axiosClient.get('settings', {
        params: { locale: activeLang },
      });
      const data = unwrapApiObject(response);

      const persistedLang = (() => {
        try {
          return localStorage.getItem('app_language');
        } catch (e) {
          return null;
        }
      })();

      const resolvedLang =
        persistedLang === 'vi' || persistedLang === 'en'
          ? persistedLang
          : ['vi', 'en'].includes(data?.general?.language)
          ? data.general.language
          : get().language || 'vi';

      if (!persistedLang) {
        try {
          localStorage.setItem('app_language', resolvedLang);
        } catch (e) {
          // ignore
        }
      }

      if (typeof document !== 'undefined') {
        document.documentElement.lang = resolvedLang;
      }

      const remoteTranslations = data?.translations || {};
      const mergedVi = deepMerge(
        viLocales,
        remoteTranslations.vi || (resolvedLang === 'vi' && !remoteTranslations.en ? remoteTranslations : {})
      );
      const mergedEn = deepMerge(
        enLocales,
        remoteTranslations.en || (resolvedLang === 'en' && !remoteTranslations.vi ? remoteTranslations : {})
      );

      const defaultCurrencyCode = data?.general?.default_currency || get().currency?.code || 'VND';
      const defaultCurrencySymbol =
        data?.general?.currency_symbol ||
        CURRENCY_SYMBOLS[defaultCurrencyCode] ||
        get().currency?.symbol ||
        '₫';

      set({
        settings: data,
        language: resolvedLang,
        currency: {
          code: defaultCurrencyCode,
          symbol: defaultCurrencySymbol,
        },
        translations: {
          vi: mergedVi,
          en: mergedEn,
        },
        loading: false,
      });

      // Apply theme CSS variables if available
      if (typeof document !== 'undefined' && data?.theme) {
        const root = document.documentElement;
        if (data.theme.primary_color) {
          root.style.setProperty('--color-primary', data.theme.primary_color);
        }
        if (data.theme.secondary_color) {
          root.style.setProperty('--color-secondary', data.theme.secondary_color);
        }
        if (data.theme.font_family) {
          root.style.setProperty('--font-primary', data.theme.font_family);
        }
      }
    } catch (error) {
      console.error('Failed to fetch settings:', error);
      set({ loading: false });
    }
  },

  setLanguage: (lang) => {
    const validLang = ['vi', 'en'].includes(lang) ? lang : 'vi';
    try {
      localStorage.setItem('app_language', validLang);
    } catch (e) {
      // ignore
    }
    if (typeof document !== 'undefined') {
      document.documentElement.lang = validLang;
    }
    set({ language: validLang });
    get().fetchSettings(validLang);
    try {
      const menuState = useMenuStore.getState();
      if (menuState?.fetchMainMenu) {
        menuState.fetchMainMenu();
      } else if (menuState?.fetchMenus) {
        menuState.fetchMenus();
      }
    } catch (e) {
      // ignore
    }
  },

  setCurrency: (currencyInput) => {
    if (!currencyInput) return;
    if (typeof currencyInput === 'string') {
      const code = currencyInput.toUpperCase();
      const currenciesList = get().settings?.currencies || [];
      const matched = currenciesList.find((c) => c.code === code);
      const symbol = matched?.symbol || CURRENCY_SYMBOLS[code] || (code === 'USD' ? '$' : '₫');
      set({ currency: { code, symbol } });
    } else if (typeof currencyInput === 'object' && currencyInput.code) {
      const code = currencyInput.code.toUpperCase();
      const symbol = currencyInput.symbol || CURRENCY_SYMBOLS[code] || '₫';
      set({ currency: { code, symbol } });
    }
  },

  translate: (key, params = {}) => {
    if (!key || typeof key !== 'string') return '';
    const { language, translations } = get();
    const activeLang = language || 'vi';

    const value =
      resolveKey(translations?.[activeLang], key) ??
      resolveKey(activeLang === 'vi' ? viLocales : enLocales, key) ??
      resolveKey(translations?.en, key) ??
      resolveKey(translations?.vi, key) ??
      resolveKey(enLocales, key) ??
      resolveKey(viLocales, key);

    if (value !== undefined) {
      return interpolate(value, params);
    }

    return '';
  },

  getSetting: (key) => {
    if (!key) return null;
    const { settings } = get();
    const keys = key.split('.');
    let result = settings;
    for (const k of keys) {
      if (result && result[k] !== undefined) {
        result = result[k];
      } else {
        return null;
      }
    }
    return result;
  },
}));

export default useSettingsStore;
