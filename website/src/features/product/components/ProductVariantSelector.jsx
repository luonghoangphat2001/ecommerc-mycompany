import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';

const ProductVariantSelector = ({ variants, selectedVariant, onVariantChange }) => {
  const { translate } = useTranslation('product');

  const containerClass = "space-y-4";
  const sectionTitleClass = "text-sm font-medium text-gray-700";
  const optionsContainerClass = "flex flex-wrap gap-2";
  const optionClass = "px-4 py-2 border rounded-lg cursor-pointer transition hover:border-blue-500";
  const selectedClass = "border-blue-500 bg-blue-50 text-blue-700";
  const disabledClass = "opacity-50 cursor-not-allowed";

  if (!variants || variants.length === 0) return null;

  return (
    <div className={containerClass}>
      {variants.map((variant) => (
        <div key={variant.id}>
          <h3 className={sectionTitleClass}>{variant.name}</h3>
          <div className={optionsContainerClass}>
            {variant.options.map((option) => {
              const isSelected = selectedVariant?.[variant.id] === option.id;
              const isDisabled = !option.in_stock;
              
              return (
                <button
                  key={option.id}
                  className={`${optionClass} ${isSelected ? selectedClass : ''} ${isDisabled ? disabledClass : ''}`}
                  onClick={() => !isDisabled && onVariantChange(variant.id, option.id)}
                  disabled={isDisabled}
                >
                  {option.label}
                </button>
              );
            })}
          </div>
        </div>
      ))}
    </div>
  );
};

export default ProductVariantSelector;
