import React from 'react';
import { useTranslation } from 'react-i18next';
import { useCartSuggestions } from '../hooks/useCartSuggestions';
import ProductCard from '../../product/components/ProductCard';

const CartSuggestions = () => {
  const { translate } = useTranslation('cart');
  const { data: suggestions, isLoading } = useCartSuggestions();

  const containerClass = "mt-8";
  const titleClass = "text-xl font-bold mb-4";
  const gridClass = "grid grid-cols-2 md:grid-cols-4 gap-4";
  const emptyClass = "text-center text-gray-500 py-4";

  if (!suggestions || suggestions.length === 0) return null;

  return (
    <div className={containerClass}>
      <h2 className={titleClass}>{translate('suggestions')}</h2>
      
      {isLoading ? (
        <div className={emptyClass}>{translate('common.loading')}</div>
      ) : (
        <div className={gridClass}>
          {suggestions.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </div>
  );
};

export default CartSuggestions;
