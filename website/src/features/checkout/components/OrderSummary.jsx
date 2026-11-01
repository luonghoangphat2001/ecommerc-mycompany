import React from "react"
import { ShoppingBag, ShieldCheck, Package } from "lucide-react"
import { Card, Button } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"

const OrderSummary = React.memo(({ items, summary = {}, isSubmitting }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency, getLocalizedText } = useFormatters()

    const subtotal = summary?.subtotal ?? 0
    const shippingCost = summary?.shipping?.amount ?? summary?.shipping_cost ?? 0
    const taxAmount = summary?.tax?.amount ?? summary?.tax_amount ?? 0
    const discountAmount =
        summary?.discount?.amount ??
        summary?.discount_amount ??
        (typeof summary?.discount === "number" ? summary.discount : 0) ??
        0
    const grandTotal = summary?.total ?? 0

    return (
        <div className="lg:col-span-1">
            <Card shadow="sm" className="sticky top-28 tabular-nums">
                <Card.Header>
                    <Card.Title>{translate("cart.summary_title")}</Card.Title>
                </Card.Header>

                <Card.Body>
                    {/* Items list */}
                    <div className="max-h-64 overflow-y-auto mb-5 pr-1 space-y-3">
                        {items.map((item) => {
                            const itemImage = item.image?.url || item.image || item.image_url
                            return (
                                <div key={item.id} className="flex items-center gap-3">
                                    <div className="w-12 h-12 bg-slate-100 rounded-lg border border-slate-200/70 overflow-hidden flex-shrink-0 flex items-center justify-center text-slate-400">
                                        {itemImage ? (
                                            <img
                                                src={itemImage}
                                                alt={getLocalizedText(item.name)}
                                                className="w-full h-full object-cover"
                                                onError={(e) => {
                                                    e.currentTarget.onerror = null
                                                    e.currentTarget.style.display = "none"
                                                    if (e.currentTarget.nextSibling) {
                                                        e.currentTarget.nextSibling.style.display = "flex"
                                                    }
                                                }}
                                            />
                                        ) : null}
                                        <div
                                            className={`w-full h-full items-center justify-center text-slate-400 ${
                                                itemImage ? "hidden" : "flex"
                                            }`}
                                        >
                                            <Package size={18} />
                                        </div>
                                    </div>
                                    <div className="flex-1 min-w-0">
                                    <p className="text-xs font-semibold text-[#09253E] truncate">
                                        {getLocalizedText(item.name)}
                                    </p>
                                    <p className="text-[11px] text-slate-500">
                                        {translate("order.quantity") || "Qty"}: {item.quantity}
                                    </p>
                                </div>
                                    <p className="text-xs font-bold text-slate-900">
                                        {formatCurrency(item.subtotal ?? (item.effective_price ?? item.price ?? 0) * item.quantity)}
                                    </p>
                                </div>
                            )
                        })}
                    </div>

                    {/* Totals breakdown */}
                    <div className="space-y-2.5 pt-4 border-t border-slate-100 text-sm">
                        <div className="flex justify-between text-slate-600">
                            <span>{translate("cart.subtotal") || "Subtotal"}</span>
                            <span className="font-semibold text-slate-900">{formatCurrency(subtotal)}</span>
                        </div>

                        {discountAmount > 0 && (
                            <div className="flex justify-between text-emerald-700">
                                <span>{translate("cart.discount") || "Discount"}</span>
                                <span className="font-semibold">-{formatCurrency(discountAmount)}</span>
                            </div>
                        )}

                        <div className="flex justify-between text-slate-600">
                            <span>{translate("cart.shipping")}</span>
                            <span className={`font-semibold ${shippingCost === 0 ? "text-emerald-700" : "text-slate-900"}`}>
                                {shippingCost > 0 ? formatCurrency(shippingCost) : translate("cart.free")}
                            </span>
                        </div>

                        {taxAmount > 0 && (
                            <div className="flex justify-between text-slate-600">
                                <span>{translate("cart.tax") || "Tax"}</span>
                                <span className="font-semibold text-slate-900">{formatCurrency(taxAmount)}</span>
                            </div>
                        )}

                        <div className="flex justify-between pt-3.5 border-t border-slate-200 items-baseline">
                            <span className="text-base font-bold text-[#09253E]">{translate("cart.total")}</span>
                            <span className="text-xl font-extrabold text-[#004687]">
                                {formatCurrency(grandTotal)}
                            </span>
                        </div>
                    </div>
                </Card.Body>

                <Card.Footer>
                    <Button
                        type="submit"
                        variant="primary"
                        size="block"
                        disabled={isSubmitting}
                        className="gap-2 h-11"
                    >
                        <ShoppingBag size={17} />
                        <span>
                            {isSubmitting
                                ? translate("checkout.order_processing")
                                : translate("checkout.place_order")}
                        </span>
                    </Button>
                    <div className="mt-3 flex items-center justify-center gap-1.5 text-[11px] text-slate-400">
                        <ShieldCheck size={13} className="text-[#004687]" />
                        <span>{translate("checkout.protected_transaction") || "Protected transaction"}</span>
                    </div>
                </Card.Footer>
            </Card>
        </div>
    )
})

export default OrderSummary
