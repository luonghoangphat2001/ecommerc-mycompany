import React from "react"
import { Trash2, Plus, Minus, AlertCircle, CheckCircle2, MapPin } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"

const CartItem = ({ item, onUpdateQuantity, onRemove, notification }) => {
  const { formatCurrency } = useFormatters()
  const imageSrc = item.image?.url || item.image || item.image_url
  const isAvailable = item.available !== false && item.stock > 0
  const subtotal = item.subtotal || (item.price * item.quantity)
  
  // Get notification for this item if any
  const itemNotification = notification || item.notification
  
  // Get selected warehouse info
  const selectedWarehouse = item.selectedWarehouse
  
  return (
      <div className={`bg-white/60 backdrop-blur-xl border rounded-[2rem] p-6 flex items-center gap-6 shadow-[0_8px_30px_rgba(0,0,0,0.02)] transition-all ${
          !isAvailable ? 'border-red-200 bg-red-50/30' : 'border-white/60'
      }`}>
          {/* Product Image */}
          <div className="w-24 h-24 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0 relative">
              <img
                  src={imageSrc || "/placeholder-product.jpg"}
                  alt={item.name}
                  className="w-full h-full object-cover"
                  onError={(e) => {
                      e.target.src = "/placeholder-product.jpg"
                  }}
              />
              {!isAvailable && (
                  <div className="absolute inset-0 bg-black/40 flex items-center justify-center">
                      <span className="text-white text-xs font-bold px-2 py-1 bg-red-500 rounded">Hết hàng</span>
                  </div>
              )}
          </div>

          {/* Product Info */}
          <div className="flex-1 min-w-0">
              <h3 className="font-bold text-slate-800 mb-1 truncate">{item.name}</h3>
              <p className="text-blue-600 font-bold">{formatCurrency(item.price)}</p>
              
              {/* Selected Warehouse */}
              {selectedWarehouse && (
                <div className="flex items-center gap-1 text-xs text-slate-500 mt-1">
                  <MapPin size={12} />
                  <span>{selectedWarehouse.warehouse_name} - Còn {selectedWarehouse.quantity}</span>
                </div>
              )}
              
              {/* Subtotal */}
              <p className="text-slate-500 text-sm mt-1">
                  Thành tiền: <span className="font-medium text-slate-700">{formatCurrency(subtotal)}</span>
              </p>
              
              {/* Notification */}
              {itemNotification && (
                  <div className={`flex items-center gap-1.5 mt-2 text-xs ${
                      itemNotification.type === 'price_change' ? 'text-amber-600' :
                      itemNotification.type === 'stock_adjusted' ? 'text-blue-600' :
                      itemNotification.type === 'out_of_stock' ? 'text-red-600' :
                      'text-slate-500'
                  }`}>
                      <AlertCircle size={14} />
                      <span>{itemNotification.message}</span>
                  </div>
              )}
                
                {/* Availability Status */}
                {isAvailable && item.stock <= 5 && (
                    <div className="flex items-center gap-1.5 mt-2 text-xs text-amber-600">
                        <AlertCircle size={14} />
                        <span>Chỉ còn {item.stock} sản phẩm</span>
                    </div>
                )}
            </div>

            {/* Quantity Control */}
            <div className="flex items-center bg-slate-50 rounded-full px-3 py-1.5 border border-slate-100">
                <button 
                    onClick={() => onUpdateQuantity(item.id, item.quantity - 1)} 
                    disabled={item.quantity <= 1}
                    className="p-1 text-slate-500 hover:text-blue-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <Minus size={16} />
                </button>
                <span className="w-10 text-center font-bold text-slate-800 text-sm">{item.quantity}</span>
                <button 
                    onClick={() => onUpdateQuantity(item.id, item.quantity + 1)} 
                    disabled={item.quantity >= item.stock}
                    className="p-1 text-slate-500 hover:text-blue-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <Plus size={16} />
                </button>
            </div>

            {/* Remove Button */}
            <button 
                onClick={() => onRemove(item.id)} 
                className="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-all"
            >
                <Trash2 size={20} />
            </button>
        </div>
    )
}

export default CartItem
