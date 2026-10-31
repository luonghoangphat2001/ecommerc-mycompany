import React from "react"
import { Trash2, Plus, Minus, AlertCircle, MapPin } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"

const CartItem = ({ item, onUpdateQuantity, onRemove, notification }) => {
    const { formatCurrency } = useFormatters()
    const imageSrc = item.image?.url || item.image || item.image_url
    const isAvailable = item.available !== false && (item.stock === undefined || item.stock > 0)
    const subtotal = item.subtotal || item.price * item.quantity
    const itemNotification = notification || item.notification
    const selectedWarehouse = item.selectedWarehouse

    return (
        <div
            className={`bg-white border rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4 shadow-card transition-all ${
                !isAvailable ? "border-red-200 bg-red-50/20" : "border-slate-200/90"
            }`}
        >
            <div className="flex items-start sm:items-center gap-4 flex-1 min-w-0">
                {/* Product Image */}
                <div className="w-20 h-20 bg-slate-100 rounded-lg overflow-hidden border border-slate-200/70 flex-shrink-0 relative">
                    <img
                        src={imageSrc || "/placeholder-product.jpg"}
                        alt={item.name}
                        className="w-full h-full object-cover"
                        onError={(e) => {
                            e.target.src = "/placeholder-product.jpg"
                        }}
                    />
                    {!isAvailable && (
                        <div className="absolute inset-0 bg-slate-900/60 flex items-center justify-center">
                            <span className="text-white text-[10px] font-bold px-1.5 py-0.5 bg-red-600 rounded">
                                Out of Stock
                            </span>
                        </div>
                    )}
                </div>

                {/* Product Info */}
                <div className="flex-1 min-w-0 tabular-nums">
                    <h3 className="font-bold text-[#09253E] text-sm sm:text-base mb-1 truncate">{item.name}</h3>
                    <p className="text-[#004687] font-bold text-sm">{formatCurrency(item.price)}</p>

                    {selectedWarehouse && (
                        <div className="flex items-center gap-1 text-xs text-slate-500 mt-1">
                            <MapPin size={12} className="text-[#004687]" />
                            <span>
                                {selectedWarehouse.warehouse_name} ({selectedWarehouse.quantity} available)
                            </span>
                        </div>
                    )}

                    <p className="text-slate-500 text-xs mt-1">
                        Line Total: <span className="font-semibold text-slate-800">{formatCurrency(subtotal)}</span>
                    </p>

                    {itemNotification && (
                        <div
                            className={`flex items-center gap-1.5 mt-1.5 text-xs ${
                                itemNotification.type === "price_change"
                                    ? "text-amber-700"
                                    : itemNotification.type === "stock_adjusted"
                                    ? "text-[#004687]"
                                    : itemNotification.type === "out_of_stock"
                                    ? "text-red-600"
                                    : "text-slate-500"
                            }`}
                        >
                            <AlertCircle size={13} />
                            <span>{itemNotification.message}</span>
                        </div>
                    )}

                    {isAvailable && item.stock !== undefined && item.stock <= 5 && (
                        <div className="flex items-center gap-1.5 mt-1.5 text-xs text-amber-700 font-medium">
                            <AlertCircle size={13} />
                            <span>Only {item.stock} units remaining</span>
                        </div>
                    )}
                </div>
            </div>

            {/* Quantity & Remove Actions */}
            <div className="flex items-center justify-between sm:justify-end gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                <div className="inline-flex items-center bg-white rounded-lg border border-slate-300 h-9 px-0.5 tabular-nums">
                    <button
                        type="button"
                        onClick={() => onUpdateQuantity(item.id, item.quantity - 1)}
                        disabled={item.quantity <= 1}
                        className="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-[#004687] hover:bg-slate-100 rounded-md transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <Minus size={14} />
                    </button>
                    <span className="w-9 text-center font-bold text-slate-900 text-xs">{item.quantity}</span>
                    <button
                        type="button"
                        onClick={() => onUpdateQuantity(item.id, item.quantity + 1)}
                        disabled={item.stock !== undefined && item.quantity >= item.stock}
                        className="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-[#004687] hover:bg-slate-100 rounded-md transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <Plus size={14} />
                    </button>
                </div>

                <button
                    type="button"
                    onClick={() => onRemove(item.id)}
                    aria-label="Remove item"
                    className="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                >
                    <Trash2 size={17} />
                </button>
            </div>
        </div>
    )
}

export default CartItem
