import { create } from 'zustand';
import axiosClient from '../api/axiosClient';
import { translations as localTranslations } from '../i18n/translations';

const useSettingsStore = create((set, get) => ({
  settings: null,
  loading: false,
  language: 'vi',
  currency: {
    code: 'VND',
    symbol: '₫'
  },
  apiTranslations: {},

  fetchSettings: async () => {
    set({ loading: true });
    try {
      const response = await axiosClient.get('storefront-settings');
      const data = response.data;
      
      set({ 
        settings: data, 
        language: data.general?.language || 'vi',
        currency: {
          code: data.general?.default_currency || 'VND',
          symbol: data.general?.currency_symbol || '₫'
        },
        apiTranslations: data.translations || {},
        loading: false 
      });
    } catch (error) {
      console.error('Failed to fetch settings:', error);
      set({ loading: false });
    }
  },

  setLanguage: (lang) => set({ language: lang }),

  t: (key) => {
    const { language, apiTranslations } = get();
    const keys = key.split('.');
    
    // Try API translations first
    let result = apiTranslations[language];
    let foundInApi = true;
    for (const k of keys) {
      if (result && result[k]) {
        result = result[k];
      } else {
        foundInApi = false;
        break;
      }
    }

    if (foundInApi) return result;

    // Fallback to local translations
    result = localTranslations[language];
    for (const k of keys) {
      if (result && result[k]) {
        result = result[k];
      } else {
        return key; // Fallback to key if not found
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
