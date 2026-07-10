import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';
import { Link } from 'react-router-dom';
import { X, ShoppingBag, Truck, MapPin } from 'lucide-react';
import { formatCurrency } from '../../../utils/formatters';
import useCartStore from '../store/useCartStore';
import { useCartActions } from '../hooks/useCartActions';

const MiniCart = ({ isOpen, onClose }) => {
  const translate = useSettingsStore(state => state.translate);
  const { items, summary, getCartCount } = useCartStore();
  const { removeCartItem, updateCartItem } = useCartActions();
  
  const count = getCartCount?.() || items.length;
  const total = summary?.total || items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  const subtotal = summary?.subtotal || total;
  
  if (!isOpen) return null;
  
  return (
    <>
      {/* Backdrop */}
      <div 
        className="fixed inset-0 bg-black/40 z-40 transition-opacity"
        onClick={onClose}
      />
       
      {/* Cart Panel */}
      <div className="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl z-50 flex flex-col animate-slideInRight">
        {/* Header */}
        <div className="flex items-center justify-between p-6 border-b">
          <div className="flex items-center gap-3">
            <ShoppingBag className="w-6 h-6 text-blue-600" />
            <h2 className="text-xl font-bold text-slate-900">
              {translate('cart.title')} 
              <span className="ml-2 text-sm font-normal text-slate-500">({count} sản phẩm)</span>
            </h2>
          </div>
          <button 
            onClick={onClose} 
            className="p-2 hover:bg-slate-100 rounded-full transition-colors"
          >
            <X size={24} className="text-slate-500" />
          </button>
        </div>
  
        {/* Content */}
        <div className="flex-1 overflow-y-auto p-6">
          {items.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-full text-center">
              <ShoppingBag size={64} className="text-slate-200 mb-4" />
              <p className="text-slate-500 mb-4">{translate('cart.empty')}</p>
              <Link 
                to="/shop" 
                onClick={onClose}
                className="text-blue-600 font-medium hover:underline"
              >
                {translate('cart.continue_shopping')}
              </Link>
            </div>
          ) : (
            <div className="space-y-4">
              {items.map((item) => (
                <div key={item.id} className="flex gap-4 p-4 bg-slate-50 rounded-2xl">
                  <div className="w-20 h-20 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0">
                      <img 
                        src={item.image?.url || item.image || item.image_url || "/placeholder-product.jpg"}
                        alt={item.name}
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          e.target.src = "/placeholder-product.jpg"
                        }}
                      />
                  </div>
                  <div className="flex-1 min-w-0">
                    <h3 className="font-bold text-slate-800 truncate">{item.name}</h3>
                    <p className="text-blue-600 font-bold">{formatCurrency(item.price)}</p>
                     
                    {/* Warehouse Info */}
                    {item.selectedWarehouse && (
                      <div className="flex items-center gap-1 text-xs text-slate-500 mt-1">
                        <MapPin size={12} />
                        <span>{item.selectedWarehouse.warehouse_name}</span>
                      </div>
                    )}
                     
                    <div className="flex items-center gap-2 mt-2">
                      <button 
                        className="w-8 h-8 flex items-center justify-center border border-slate-300 rounded-lg hover:bg-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        onClick={() => updateCartItem({ itemId: item.id, quantity: item.quantity - 1 })}
                        disabled={item.quantity <= 1}
                      >
                        -
                      </button>
                      <span className="w-10 text-center font-medium">{item.quantity}</span>
                      <button 
                        className="w-8 h-8 flex items-center justify-center border border-slate-300 rounded-lg hover:bg-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        onClick={() => updateCartItem({ itemId: item.id, quantity: item.quantity + 1 })}
                        disabled={item.quantity >= (item.stock || 99)}
                      >
                        +
                      </button>
                    </div>
                  </div>
                  <div className="flex flex-col items-end justify-between">
                    <p className="font-bold text-slate-900">{formatCurrency(item.price * item.quantity)}</p>
                    <button 
                      onClick={() => removeCartItem(item.id)}
                      className="p-2 text-slate-400 hover:text-red-500 transition-colors"
                    >
                      <X size={16} />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
  
        {/* Footer */}
        {items.length > 0 && (
          <div className="p-6 border-t bg-slate-50">
            {/* Summary */}
            <div className="space-y-2 mb-4">
              <div className="flex justify-between text-slate-500 font-medium">
                <span>{translate('cart.subtotal')}</span>
                <span>{formatCurrency(subtotal)}</span>
              </div>
              {summary?.shipping?.amount > 0 && (
                <div className="flex justify-between text-slate-500 font-medium">
                  <div className="flex items-center gap-1">
                    <Truck size={14} />
                    <span>Vận chuyển</span>
                  </div>
                  <span className={summary.shipping.amount > 0 ? "" : "text-green-600"}>
                    {summary.shipping.amount > 0 ? formatCurrency(summary.shipping.amount) : "Miễn phí"}
                  </span>
                </div>
              )}
              {summary?.tax?.amount > 0 && (
                <div className="flex justify-between text-slate-500 font-medium">
                  <span>Thuế</span>
                  <span>{formatCurrency(summary.tax.amount)}</span>
                </div>
              )}
              <div className="flex justify-between pt-4 border-t border-slate-200 items-center">
                <span className="font-bold text-slate-900">Tổng cộng</span>
                <span className="text-2xl font-black text-blue-600">{formatCurrency(total)}</span>
              </div>
            </div>
             
            {/* Buttons */}
            <Link
              to="/checkout"
              onClick={onClose}
              className="block w-full bg-blue-600 text-white py-4 rounded-xl font-bold text-center hover:bg-blue-700 transition-colors mb-3"
            >
              {translate('cart.checkout_button')}
            </Link>
            <button 
              onClick={onClose}
              className="block w-full text-slate-500 py-3 font-medium hover:text-slate-700 transition-colors"
            >
              {translate('cart.continue_shopping')}
            </button>
          </div>
        )}
      </div>
    </>
  );
};

export default MiniCart;
