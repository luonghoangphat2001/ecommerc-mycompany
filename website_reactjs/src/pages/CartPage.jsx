import React from 'react';
import { Link } from 'react-router-dom';
import { ShoppingBag } from 'lucide-react';
import useCartStore from '../features/cart/store/useCartStore';
import useSettingsStore from '../store/useSettingsStore';
import CartItem from '../features/cart/components/CartItem';
import CartSummary from '../features/cart/components/CartSummary';

const CartPage = () => {
  const { items, updateQuantity, removeFromCart, getCartTotal } = useCartStore();
  const t = useSettingsStore((state) => state.t);

  if (items.length === 0) {
    return (
      <div className="w-full py-20 flex flex-col items-center justify-center text-center">
        <div className="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6">
          <ShoppingBag size={40} />
        </div>
        <h2 className="text-2xl font-bold text-slate-800 mb-2">{t('cart.empty_title')}</h2>
        <p className="text-slate-500 mb-8 max-w-xs mx-auto">{t('cart.empty_subtitle')}</p>
        <Link 
          to="/shop" 
          className="bg-slate-900 text-white font-bold px-8 py-3.5 rounded-2xl hover:bg-blue-600 transition-all shadow-xl shadow-slate-200"
        >
          {t('cart.continue_shopping')}
        </Link>
      </div>
    );
  }

  return (
    <div className="w-full max-w-6xl mx-auto">
      <h1 className="text-3xl font-black text-slate-900 mb-10 tracking-tight">{t('cart.page_title')}</h1>
      
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div className="lg:col-span-2 space-y-4">
          {items.map((item) => (
            <CartItem 
              key={item.id} 
              item={item} 
              onUpdateQuantity={updateQuantity} 
              onRemove={removeFromCart} 
            />
          ))}
        </div>

        <div className="lg:col-span-1">
          <CartSummary total={getCartTotal()} t={t} />
        </div>
      </div>
    </div>
  );
};

export default CartPage;


