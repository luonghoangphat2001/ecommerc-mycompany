import { create } from 'zustand';
import axiosClient from '../api/axiosClient';
import { unwrapApiObject } from '../api/apiResponse';

const useSettingsStore = create((set, get) => ({
  settings: null,
  loading: false,
  language: 'vi',
  currency: {
    code: 'VND',
    symbol: '₫'
  },
  translations: {},

  fetchSettings: async () => {
    set({ loading: true });
    try {
      const response = await axiosClient.get('storefront-settings');
      const data = unwrapApiObject(response);

      set({
        settings: data,
        language: data.general?.language || 'vi',
        currency: {
          code: data.general?.default_currency || 'VND',
          symbol: data.general?.currency_symbol || '₫'
        },
        translations: data.translations || {},
        loading: false
      });
    } catch (error) {
      console.error('Failed to fetch settings:', error);
      set({ loading: false });
    }
  },

  setLanguage: (lang) => set({ language: lang }),

  translate: (key) => {
    const { language, translations } = get();
    const keys = key.split('.');

    let result = translations[language];
    for (const keyPart of keys) {
      if (result && result[keyPart]) {
        result = result[keyPart];
      } else {
        return key; // Return key if translation not found
      }
    }
    return result;
  },

  getSetting: (key) => {
    const { settings } = get();
    // Support nested keys like 'general.store_name'
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
  }
}));

export default useSettingsStore;
