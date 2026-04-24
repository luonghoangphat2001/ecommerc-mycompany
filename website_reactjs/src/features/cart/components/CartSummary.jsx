import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import { formatCurrency } from '../../../utils/format';

const CartSummary = ({ total, t }) => {
  return (
    <div className="bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-8 shadow-[0_20px_50px_rgba(0,0,0,0.04)] sticky top-32">
      <h2 className="text-xl font-bold text-slate-900 mb-6">{t('cart.summary_title')}</h2>
      
      <div className="space-y-4 mb-8">
        <div className="flex justify-between text-slate-500 font-medium">
          <span>{t('cart.subtotal')}</span>
          <span>{formatCurrency(total)}</span>
        </div>
        <div className="flex justify-between text-slate-500 font-medium">
          <span>{t('cart.shipping')}</span>
          <span className="text-blue-600">{t('cart.free')}</span>
        </div>
        <div className="pt-4 border-t border-slate-100 flex justify-between items-center">
          <span className="text-lg font-bold text-slate-900">{t('cart.total')}</span>
          <span className="text-2xl font-black text-blue-600">{formatCurrency(total)}</span>
        </div>
      </div>

      <Link 
        to="/checkout"
        className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl flex items-center justify-center gap-2 shadow-xl shadow-blue-100 hover:shadow-blue-200 transition-all hover:-translate-y-0.5 active:scale-95"
      >
        {t('cart.checkout_button')}
        <ArrowRight size={20} />
      </Link>
      
      <Link 
        to="/shop"
        className="w-full flex items-center justify-center mt-6 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors"
      >
        {t('cart.continue_shopping')}
      </Link>
    </div>
  );
};

export default CartSummary;
