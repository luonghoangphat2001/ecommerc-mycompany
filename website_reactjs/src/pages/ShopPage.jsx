import React from 'react';
import { useProducts } from '../features/product/hooks/useProducts';
import ProductCard from '../features/product/components/ProductCard';
import Loading from '../components/common/Loading';
import Error from '../components/common/Error';
import useSettingsStore from '../store/useSettingsStore';

const ShopPage = () => {
  const { data: productsData, isLoading, isError, error } = useProducts();
  const t = useSettingsStore((state) => state.t);

  if (isLoading) return <Loading message={t('shop.loading')} />;
  if (isError) return <Error message={`${t('shop.error')} ${error?.message}`} />;

  const products = productsData?.data || [];

  return (
    <div className="w-full">
      <div className="mb-10 text-center">
        <h1 className="text-4xl font-bold text-slate-800 tracking-tight mb-4">{t('shop.title')}</h1>
        <p className="text-slate-500 max-w-2xl mx-auto">{t('shop.subtitle')}</p>
      </div>

      {products.length === 0 ? (
        <div className="text-center py-20 text-slate-500">{t('shop.empty')}</div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </div>
  );
};

export default ShopPage;


