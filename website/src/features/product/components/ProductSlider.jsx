import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';
import ProductCard from './ProductCard';

const ProductSlider = ({ products, title, loading }) => {
  const { translate } = useTranslation('product');

  const containerClass = "w-full";
  const headerClass = "flex items-center justify-between mb-4";
  const titleClass = "text-xl font-bold text-gray-900";
  const sliderClass = "flex gap-4 overflow-x-auto pb-4 scrollbar-hide";
  const emptyClass = "text-center text-gray-500 py-8";

  if (!products || products.length === 0) return null;

  return (
    <div className={containerClass}>
      <div className={headerClass}>
        <h2 className={titleClass}>{title}</h2>
      </div>
      
      {loading ? (
        <div className={emptyClass}>{translate('common.loading')}</div>
      ) : (
        <div className={sliderClass}>
          {products.map((product) => (
            <div key={product.id} className="flex-shrink-0 w-64">
              <ProductCard product={product} />
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default ProductSlider;
