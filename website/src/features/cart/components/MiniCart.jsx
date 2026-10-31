import React from "react"
import { Link } from "react-router-dom"
import { X, ShoppingBag, Truck, MapPin, Minus, Plus, ArrowRight } from "lucide-react"
import { formatCurrency } from "../../../utils/formatters"
import useCartStore from "../store/useCartStore"
import { useCartActions } from "../hooks/useCartActions"
import useSettingsStore from "../../../store/useSettingsStore"

const MiniCart = ({ isOpen, onClose }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { items, summary, getCartCount } = useCartStore()
    const { removeCartItem, updateCartItem } = useCartActions()

    const count = getCartCount?.() || items.length
    const total = summary?.total || items.reduce((sum, item) => sum + item.price * item.quantity, 0)
    const subtotal = summary?.subtotal || total

    if (!isOpen) return null

    return (
        <>
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-slate-900/40 backdrop-blur-[1px] z-40 transition-opacity"
                onClick={onClose}
            />

            {/* Cart Drawer */}
            <div className="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-modal border-l border-slate-200 z-50 flex flex-col animate-slideInRight tabular-nums">
                {/* Header */}
                <div className="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-lg bg-[#004687]/10 text-[#004687] flex items-center justify-center">
                            <ShoppingBag size={17} />
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-[#09253E] leading-tight">
                                {translate("cart.title")}
                            </h2>
                            <span className="text-xs text-slate-500">
                                {count} {translate("cart.items")}
                            </span>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Close Cart"
                        className="p-1.5 hover:bg-slate-100 rounded-lg text-slate-500 transition-colors"
                    >
                        <X size={20} />
                    </button>
                </div>

                {/* Items List */}
                <div className="flex-1 overflow-y-auto p-5">
                    {items.length === 0 ? (
                        <div className="flex flex-col items-center justify-center h-full text-center py-12">
                            <div className="w-14 h-14 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                <ShoppingBag size={28} />
                            </div>
                            <p className="text-sm font-medium text-slate-600 mb-4">{translate("cart.empty")}</p>
                            <Link
                                to="/shop"
                                onClick={onClose}
                                className="px-4 py-2 bg-[#004687] text-white text-xs font-semibold rounded-lg hover:bg-[#003669] transition-colors"
                            >
                                {translate("cart.continue_shopping")}
                            </Link>
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {items.map((item) => (
                                <div
                                    key={item.id}
                                    className="flex gap-3.5 p-3.5 bg-white border border-slate-200/90 rounded-xl shadow-card"
                                >
                                    <div className="w-16 h-16 bg-slate-100 rounded-lg border border-slate-200/60 overflow-hidden flex-shrink-0">
                                        <img
                                            src={
                                                item.image?.url ||
                                                item.image ||
                                                item.image_url ||
                                                "/placeholder-product.jpg"
                                            }
                                            alt={item.name}
                                            className="w-full h-full object-cover"
                                            onError={(e) => {
                                                e.target.src = "/placeholder-product.jpg"
                                            }}
                                        />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <h3 className="text-sm font-semibold text-[#09253E] truncate">
                                            {item.name}
                                        </h3>
                                        <p className="text-xs font-bold text-[#004687] mt-0.5">
                                            {formatCurrency(item.price)}
                                        </p>

                                        {item.selectedWarehouse && (
                                            <div className="flex items-center gap-1 text-[11px] text-slate-500 mt-1">
                                                <MapPin size={11} />
                                                <span className="truncate">
                                                    {item.selectedWarehouse.warehouse_name}
                                                </span>
                                            </div>
                                        )}

                                        <div className="inline-flex items-center border border-slate-200 rounded-md mt-2 bg-slate-50">
                                            <button
                                                type="button"
                                                className="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-white rounded-l-md transition-colors disabled:opacity-40"
                                                onClick={() =>
                                                    updateCartItem({
                                                        itemId: item.id,
                                                        quantity: item.quantity - 1,
                                                    })
                                                }
                                                disabled={item.quantity <= 1}
                                            >
                                                <Minus size={12} />
                                            </button>
                                            <span className="w-7 text-center text-xs font-semibold text-slate-800">
                                                {item.quantity}
                                            </span>
                                            <button
                                                type="button"
                                                className="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-white rounded-r-md transition-colors disabled:opacity-40"
                                                onClick={() =>
                                                    updateCartItem({
                                                        itemId: item.id,
                                                        quantity: item.quantity + 1,
                                                    })
                                                }
                                                disabled={item.quantity >= (item.stock || 99)}
                                            >
                                                <Plus size={12} />
                                            </button>
                                        </div>
                                    </div>
                                    <div className="flex flex-col items-end justify-between">
                                        <p className="text-xs font-bold text-slate-900">
                                            {formatCurrency(item.price * item.quantity)}
                                        </p>
                                        <button
                                            type="button"
                                            onClick={() => removeCartItem(item.id)}
                                            className="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                        >
                                            <X size={15} />
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Footer */}
                {items.length > 0 && (
                    <div className="p-5 border-t border-slate-200 bg-slate-50/70">
                        <div className="space-y-2 mb-4 text-xs">
                            <div className="flex justify-between text-slate-600">
                                <span>{translate("cart.subtotal")}</span>
                                <span className="font-semibold text-slate-900">{formatCurrency(subtotal)}</span>
                            </div>
                            {summary?.shipping?.amount > 0 && (
                                <div className="flex justify-between text-slate-600">
                                    <div className="flex items-center gap-1">
                                        <Truck size={13} />
                                        <span>{translate("cart.shipping")}</span>
                                    </div>
                                    <span className="font-semibold text-slate-900">
                                        {formatCurrency(summary.shipping.amount)}
                                    </span>
                                </div>
                            )}
                            {summary?.tax?.amount > 0 && (
                                <div className="flex justify-between text-slate-600">
                                    <span>{translate("cart.tax")}</span>
                                    <span className="font-semibold text-slate-900">
                                        {formatCurrency(summary.tax.amount)}
                                    </span>
                                </div>
                            )}
                            <div className="flex justify-between pt-3 border-t border-slate-200 items-baseline">
                                <span className="text-sm font-bold text-[#09253E]">{translate("cart.total")}</span>
                                <span className="text-lg font-extrabold text-[#004687]">
                                    {formatCurrency(total)}
                                </span>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2.5">
                            <Link
                                to="/cart"
                                onClick={onClose}
                                className="py-2.5 px-4 rounded-lg border border-slate-300 bg-white text-slate-800 font-semibold text-xs text-center hover:bg-slate-50 transition-colors"
                            >
                                View Cart
                            </Link>
                            <Link
                                to="/checkout"
                                onClick={onClose}
                                className="py-2.5 px-4 rounded-lg bg-[#004687] text-white font-semibold text-xs text-center hover:bg-[#003669] transition-colors inline-flex items-center justify-center gap-1.5"
                            >
                                <span>{translate("cart.checkout_button")}</span>
                                <ArrowRight size={14} />
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </>
    )
}

export default MiniCart
