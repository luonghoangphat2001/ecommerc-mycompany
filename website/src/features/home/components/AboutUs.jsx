import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';

const AboutUs = ({ content }) => {
  const { translate } = useTranslation('about');

  const containerClass = "max-w-4xl mx-auto py-16 px-4";
  const titleClass = "text-3xl font-bold text-center mb-8";
  const descriptionClass = "text-lg text-gray-600 text-center leading-relaxed";

  if (!content) return null;

  return (
    <div className={containerClass}>
      <h2 className={titleClass}>{content.title || translate('title')}</h2>
      <p className={descriptionClass}>{content.description || translate('description')}</p>
    </div>
  );
};

export default AboutUs;
