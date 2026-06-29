import React from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, Package } from 'lucide-react';
import { useFormatters } from '../../../utils/useFormatters';

const RelatedProducts = ({ products, title, icon: Icon }) => {
  const { formatCurrency } = useFormatters();
  
  if (!products || products.length === 0) return null;

  const getImageUrl = (product) => {
    // Try different image sources
    if (product.image?.url) return product.image.url;
    if (product.image) return product.image;
    if (product.og_image) return product.og_image;
    return '/placeholder-product.jpg';
  };

  return (
    <div className="mt-8">
      <h3 className="text-lg font-semibold mb-4 flex items-center gap-2">
        <Icon size={20} />
        {title}
      </h3>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {products.map((item) => {
          // Handle both direct product or nested product (from upsell/cross-sell API)
          const product = item.product || item;
          
          return (
            <Link
              key={product.id}
              to={`/products/${product.slug || product.id}`}
              className="group block bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-lg transition-all"
            >
              <div className="aspect-square bg-slate-100 overflow-hidden">
                <img
                  src={getImageUrl(product)}
                  alt={product.name}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform"
                  onError={(e) => { e.target.src = '/placeholder-product.jpg'; }}
                />
              </div>
              <div className="p-3">
                <h4 className="text-sm font-medium text-slate-800 line-clamp-2 mb-1">
                  {product.name}
                </h4>
                <p className="text-sm font-bold text-blue-600">
                  {formatCurrency(product.price)}
                </p>
              </div>
            </Link>
          );
        })}
      </div>
    </div>
  );
};

export default RelatedProducts;
