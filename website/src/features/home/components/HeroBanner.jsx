import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';

const HeroBanner = ({ content }) => {
  const { translate } = useTranslation('home');

  const containerClass = "relative h-96 bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center";
  const contentClass = "text-center text-white px-4";
  const titleClass = "text-4xl md:text-5xl font-bold mb-4";
  const subtitleClass = "text-xl md:text-2xl mb-8";
  const buttonClass = "bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition";

  if (!content) return null;

  return (
    <div className={containerClass}>
      <div className={contentClass}>
        <h1 className={titleClass}>{content.title || translate('hero_title')}</h1>
        <p className={subtitleClass}>{content.subtitle || translate('hero_subtitle')}</p>
        <button className={buttonClass}>{translate('shop_now')}</button>
      </div>
    </div>
  );
};

export default HeroBanner;
