import React from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart } from 'lucide-react';
import useCartStore from '../../cart/store/useCartStore';
import useSettingsStore from '../../../store/useSettingsStore';
import { formatCurrency } from '../../../utils/format';

const ProductCard = ({ product }) => {
  const addToCart = useCartStore((state) => state.addToCart);
  const t = useSettingsStore((state) => state.t);
  const { code, symbol } = useSettingsStore((state) => state.currency);
  const formatPrice = (amount) => formatCurrency(amount, code, symbol);

  const handleAddToCart = (e) => {
    e.preventDefault();
    e.stopPropagation();
    addToCart(product, 1);
  };

  return (
    <Link 
      to={`/products/${product.id}`}
      className="group flex flex-col bg-white/40 backdrop-blur-md rounded-2xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] transition-all duration-300"
    >
      <div className="relative aspect-[4/5] bg-slate-100 overflow-hidden">
        <img
          src={product.image?.url || 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&q=80'}

          alt={product.name}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        />
        <div className="absolute top-3 left-3 flex flex-col gap-2">
          {product.is_featured && (
            <span className="px-2.5 py-1 text-xs font-semibold bg-white/90 backdrop-blur-md text-slate-800 rounded-full shadow-sm">
              {t('product.featured')}
            </span>
          )}
        </div>
      </div>
      
      <div className="flex flex-col flex-1 p-5">
        <h3 className="font-medium text-slate-800 line-clamp-1 text-sm leading-snug mb-1 group-hover:text-blue-600 transition-colors">
          {product.name}
        </h3>
        
        <div className="flex flex-col gap-1 mb-2">
          {product.brand && (
            <span className="text-[10px] uppercase tracking-wider font-bold text-slate-400">
              {product.brand.name}
            </span>
          )}
          {product.categories && product.categories.length > 0 && (
            <span className="text-[10px] text-blue-500 font-medium">
              {product.categories[0].name}
            </span>
          )}
        </div>

        
        <div className="mt-auto flex items-center justify-between pt-4">
          <span className="font-bold text-slate-900">
            {formatPrice(product.price)}
          </span>
          
          <button
            onClick={handleAddToCart}
            title={t('product.add_to_cart')}
            className="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm hover:shadow-md"
          >
            <ShoppingCart size={18} />
          </button>
        </div>
      </div>
    </Link>
  );
};

export default ProductCard;


