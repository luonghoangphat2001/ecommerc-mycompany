import React from "react"
import { formatCurrency } from "../../../utils/format"
import Card from "../../../components/common/Card"
import useSettingsStore from "../../../store/useSettingsStore"

const OrderSummary = ({ items, total, isSubmitting }) => {
    const t = useSettingsStore(state => state.t);
    const rowClass = "flex justify-between text-slate-500 font-medium";

    return (
        <div className="lg:col-span-1">
            <Card className="sticky top-32 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
                <Card.Header>
                    <Card.Title>{t('cart.summary_title')}</Card.Title>
                </Card.Header>

                <Card.Body>
                    <div className="max-h-60 overflow-y-auto mb-6 pr-2 space-y-4 custom-scrollbar">
                        {items.map((item) => (
                            <div key={item.id} className="flex gap-4">
                                <div className="w-14 h-14 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0">
                                    <img src={item.image_url} alt={item.name} className="w-full h-full object-cover" />
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-bold text-slate-800 truncate">{item.name}</p>
                                    <p className="text-xs text-slate-500">x{item.quantity}</p>
                                </div>
                                <p className="text-sm font-bold text-slate-900">{formatCurrency(item.price * item.quantity)}</p>
                            </div>
                        ))}
                    </div>

                    <div className="space-y-4 pt-6 border-t border-slate-100">
                        <div className={rowClass}>
                            <span>{t('cart.subtotal')}</span>
                            <span>{formatCurrency(total)}</span>
                        </div>
                        <div className={rowClass}>
                            <span>{t('cart.shipping')}</span>
                            <span className="text-green-600">{t('cart.free')}</span>
                        </div>
                        <div className="pt-4 border-t border-slate-100 flex justify-between items-center">
                            <span className="text-lg font-bold text-slate-900">{t('cart.total')}</span>
                            <span className="text-2xl font-black text-blue-600">{formatCurrency(total)}</span>
                        </div>
                    </div>
                </Card.Body>

                <Card.Footer>
                    <button type="submit" disabled={isSubmitting} className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl flex items-center justify-center gap-2 shadow-xl shadow-blue-100 hover:shadow-blue-200 transition-all hover:-translate-y-0.5 active:scale-95 disabled:bg-slate-300 disabled:cursor-not-allowed disabled:transform-none">
                        {isSubmitting ? t('checkout.order_processing') : t('checkout.place_order')}
                    </button>
                </Card.Footer>
            </Card>
        </div>
    )
}

export default OrderSummary;
