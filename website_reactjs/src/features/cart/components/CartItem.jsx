import React from "react"
import { Trash2, Plus, Minus } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"

const CartItem = ({ item, onUpdateQuantity, onRemove }) => {
    const { formatCurrency } = useFormatters()
    const imageSrc = item.image?.url || item.image || item.image_url

    return (
        <div className="bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2rem] p-6 flex items-center gap-6 shadow-[0_8px_30px_rgba(0,0,0,0.02)]">
            {/* Product Image */}
            <div className="w-24 h-24 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0">
                <img
                    src={imageSrc || "/placeholder-product.jpg"}
                    alt={item.name}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                        e.target.src = "/placeholder-product.jpg"
                    }}
                />
            </div>

            {/* Product Info */}
            <div className="flex-1 min-w-0">
                <h3 className="font-bold text-slate-800 mb-1 truncate">{item.name}</h3>
                <p className="text-blue-600 font-bold">{formatCurrency(item.price)}</p>
            </div>

            {/* Quantity Control */}
            <div className="flex items-center bg-slate-50 rounded-full px-3 py-1.5 border border-slate-100">
                <button onClick={() => onUpdateQuantity(item.id, item.quantity - 1)} className="p-1 text-slate-500 hover:text-blue-600 transition-colors">
                    <Minus size={16} />
                </button>
                <span className="w-8 text-center font-bold text-slate-800 text-sm">{item.quantity}</span>
                <button onClick={() => onUpdateQuantity(item.id, item.quantity + 1)} className="p-1 text-slate-500 hover:text-blue-600 transition-colors">
                    <Plus size={16} />
                </button>
            </div>

            {/* Remove Button */}
            <button onClick={() => onRemove(item.id)} className="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-all">
                <Trash2 size={20} />
            </button>
        </div>
    )
}

export default CartItem
