import { useEffect } from 'react';
import useSettingsStore from '../store/useSettingsStore';
import { getLocalizedText } from './formatters';

const upsertMeta = (attrName, attrValue, content) => {
  if (typeof document === 'undefined' || !content) return;
  let element = document.head.querySelector(`meta[${attrName}="${attrValue}"]`);
  if (!element) {
    element = document.createElement('meta');
    element.setAttribute(attrName, attrValue);
    document.head.appendChild(element);
  }
  element.setAttribute('content', String(content));
};

const upsertLink = (rel, href, hreflang = null) => {
  if (typeof document === 'undefined' || !href) return;
  const selector = hreflang
    ? `link[rel="${rel}"][hreflang="${hreflang}"]`
    : `link[rel="${rel}"]:not([hreflang])`;
  let element = document.head.querySelector(selector);
  if (!element) {
    element = document.createElement('link');
    element.setAttribute('rel', rel);
    if (hreflang) {
      element.setAttribute('hreflang', hreflang);
    }
    document.head.appendChild(element);
  }
  element.setAttribute('href', href);
};

export const useSeo = ({ title, description, ogImage, canonicalPath } = {}) => {
  const language = useSettingsStore((state) => state.language) || 'vi';
  const siteName = useSettingsStore(
    (state) =>
      state.getSetting('general.site_name') ||
      state.getSetting('general.store_name') ||
      'NovaStore'
  );
  const defaultDescription = useSettingsStore(
    (state) =>
      state.getSetting('seo.meta_description') ||
      state.translate('home.seo_description') ||
      ''
  );

  const resolvedTitle = getLocalizedText(title, language);
  const resolvedDescription = getLocalizedText(description, language) || defaultDescription;

  useEffect(() => {
    if (typeof document === 'undefined') return;

    document.documentElement.lang = language;

    const fullTitle = resolvedTitle ? `${resolvedTitle} | ${siteName}` : siteName;
    document.title = fullTitle;

    upsertMeta('name', 'description', resolvedDescription);
    upsertMeta('property', 'og:title', fullTitle);
    upsertMeta('property', 'og:description', resolvedDescription);
    upsertMeta('property', 'og:locale', language === 'en' ? 'en_US' : 'vi_VN');

    if (ogImage) {
      upsertMeta('property', 'og:image', ogImage);
    }

    if (typeof window !== 'undefined') {
      const origin = window.location.origin;
      const pathname = canonicalPath || window.location.pathname;
      const cleanUrl = `${origin}${pathname.startsWith('/') ? pathname : `/${pathname}`}`;
      const sep = cleanUrl.includes('?') ? '&' : '?';

      upsertLink('canonical', cleanUrl);
      upsertLink('alternate', `${cleanUrl}${sep}lang=vi`, 'vi');
      upsertLink('alternate', `${cleanUrl}${sep}lang=en`, 'en');
      upsertLink('alternate', cleanUrl, 'x-default');
    }
  }, [resolvedTitle, resolvedDescription, ogImage, canonicalPath, language, siteName]);
};

export default useSeo;
