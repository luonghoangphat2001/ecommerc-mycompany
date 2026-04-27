import React from 'react';
import { Trash2, Plus, Minus } from 'lucide-react';
import { formatCurrency } from '../../../utils/format';
import useSettingsStore from '../../../store/useSettingsStore';

const CartItem = ({ item, onUpdateQuantity, onRemove }) => {
  const { code, symbol } = useSettingsStore((state) => state.currency);
  const formatPrice = (amount) => formatCurrency(amount, code, symbol);

  return (
    <div className="bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2rem] p-6 flex items-center gap-6 shadow-[0_8px_30px_rgba(0,0,0,0.02)]">
      <div className="w-24 h-24 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0">
        <img 
          src={item.image?.url || item.image_url || 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&q=80'} 
          alt={item.name} 
          className="w-full h-full object-cover"
        />
      </div>
      
      <div className="flex-1 min-w-0">
        <h3 className="font-bold text-slate-800 mb-1 truncate">{item.name}</h3>
        <p className="text-blue-600 font-bold mb-0">{formatPrice(item.price)}</p>
      </div>

      <div className="flex items-center bg-slate-50 rounded-full px-3 py-1.5 border border-slate-100">
        <button 
          onClick={() => onUpdateQuantity(item.id, item.quantity - 1)}
          className="p-1 text-slate-500 hover:text-blue-600 transition-colors"
        >
          <Minus size={16} />
        </button>
        <span className="w-8 text-center font-bold text-slate-800 text-sm">
          {item.quantity}
        </span>
        <button 
          onClick={() => onUpdateQuantity(item.id, item.quantity + 1)}
          className="p-1 text-slate-500 hover:text-blue-600 transition-colors"
        >
          <Plus size={16} />
        </button>
      </div>

      <button 
        onClick={() => onRemove(item.id)}
        className="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-all"
      >
        <Trash2 size={20} />
      </button>
    </div>
  );
};

export default CartItem;
