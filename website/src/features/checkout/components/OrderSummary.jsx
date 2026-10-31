import React from "react"
import { ShoppingBag, ShieldCheck } from "lucide-react"
import { Card, Button } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"

const OrderSummary = React.memo(({ items, summary = {}, isSubmitting }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()

    const computedSubtotal = items.reduce((acc, item) => acc + item.price * item.quantity, 0)
    const subtotal = summary.subtotal || computedSubtotal
    const shippingCost = summary.shipping?.amount || 0
    const taxAmount = summary.tax?.amount || 0
    const discountAmount = summary.discount?.amount || 0
    const grandTotal = summary.total || subtotal + shippingCost + taxAmount - discountAmount

    return (
        <div className="lg:col-span-1">
            <Card shadow="sm" className="sticky top-28 tabular-nums">
                <Card.Header>
                    <Card.Title>{translate("cart.summary_title")}</Card.Title>
                </Card.Header>

                <Card.Body>
                    {/* Items list */}
                    <div className="max-h-64 overflow-y-auto mb-5 pr-1 space-y-3">
                        {items.map((item) => (
                            <div key={item.id} className="flex items-center gap-3">
                                <div className="w-12 h-12 bg-slate-100 rounded-lg border border-slate-200/70 overflow-hidden flex-shrink-0">
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
                                    <p className="text-xs font-semibold text-[#09253E] truncate">{item.name}</p>
                                    <p className="text-[11px] text-slate-500">Qty: {item.quantity}</p>
                                </div>
                                <p className="text-xs font-bold text-slate-900">
                                    {formatCurrency(item.price * item.quantity)}
                                </p>
                            </div>
                        ))}
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
                        <span>Protected transaction</span>
                    </div>
                </Card.Footer>
            </Card>
        </div>
    )
})

export default OrderSummary
