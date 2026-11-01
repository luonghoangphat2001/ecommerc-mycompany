import React from "react"
import { Link } from "react-router-dom"
import { ArrowRight, Truck, Receipt, Tag, ShieldCheck } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"
import { Card, Button } from "../../../components/common"

const CartSummary = ({ summary, total: propTotal, coupon, translate }) => {
    const { formatCurrency } = useFormatters()

    const fallbackTotal = propTotal || 0
    const resolvedSummary = summary || {
        subtotal: fallbackTotal,
        shipping: { amount: 0 },
        tax: { amount: 0 },
        total: fallbackTotal,
    }

    const subtotal = resolvedSummary.subtotal ?? fallbackTotal
    const shippingAmount = resolvedSummary.shipping?.amount ?? resolvedSummary.shipping_cost ?? 0
    const taxAmount = resolvedSummary.tax?.amount ?? resolvedSummary.tax_amount ?? 0
    const discountAmount =
        resolvedSummary.discount?.amount ??
        resolvedSummary.discount_amount ??
        (typeof resolvedSummary.discount === "number" ? resolvedSummary.discount : 0) ??
        coupon?.discount_amount ??
        0
    const total = resolvedSummary.total ?? fallbackTotal

    const hasShipping = shippingAmount > 0
    const hasTax = taxAmount > 0
    const hasDiscount = discountAmount > 0 || Boolean(coupon)

    return (
        <Card shadow="sm" className="p-6 sticky top-28 tabular-nums">
            <h2 className="text-base font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
                {translate("cart.summary_title")}
            </h2>

            <div className="space-y-3 mb-5 text-sm">
                <div className="flex justify-between text-slate-600">
                    <span>{translate("cart.subtotal")}</span>
                    <span className="font-semibold text-slate-900">{formatCurrency(subtotal)}</span>
                </div>

                <div className="flex justify-between items-center text-slate-600">
                    <div className="flex items-center gap-1.5">
                        <Truck size={15} className="text-slate-400" />
                        <span>{resolvedSummary.shipping?.method_name || translate("cart.shipping")}</span>
                    </div>
                    <span className={`font-semibold ${hasShipping ? "text-slate-900" : "text-emerald-700"}`}>
                        {hasShipping ? formatCurrency(shippingAmount) : translate("cart.free")}
                    </span>
                </div>

                {hasTax && (
                    <div className="flex justify-between items-center text-slate-600">
                        <div className="flex items-center gap-1.5">
                            <Receipt size={15} className="text-slate-400" />
                            <span>{resolvedSummary.tax?.rate_name || translate("cart.tax")}</span>
                        </div>
                        <span className="font-semibold text-slate-900">{formatCurrency(taxAmount)}</span>
                    </div>
                )}

                {hasDiscount && (
                    <div className="flex justify-between items-center text-emerald-700">
                        <div className="flex items-center gap-1.5">
                            <Tag size={15} />
                            <span>{coupon?.code || resolvedSummary.coupon?.code || translate("cart.discount")}</span>
                        </div>
                        <span className="font-semibold">
                            -{formatCurrency(discountAmount)}
                        </span>
                    </div>
                )}

                {resolvedSummary?.items_count !== undefined && (
                    <div className="flex justify-between text-slate-500 text-xs pt-1">
                        <span>{translate("cart.items_count")}</span>
                        <span>
                            {resolvedSummary.items_count} {translate("cart.items")}
                        </span>
                    </div>
                )}

                <div className="flex justify-between items-baseline text-[#09253E] font-bold text-base pt-4 border-t border-slate-200">
                    <span>{translate("cart.total")}</span>
                    <span className="text-xl font-extrabold text-[#004687]">{formatCurrency(total)}</span>
                </div>
            </div>

            {/* Free Shipping Progress */}
            {subtotal > 0 && subtotal < 500000 && (
                <div className="mb-5 p-3.5 bg-slate-50 border border-slate-200/80 rounded-lg">
                    <div className="flex items-center gap-2 text-[#004687] mb-2">
                        <Truck size={15} />
                        <span className="font-semibold text-xs">{translate("cart.free_shipping_hint")}</span>
                    </div>
                    <div className="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                        <div
                            className="bg-[#004687] h-1.5 rounded-full transition-all"
                            style={{ width: `${Math.min((subtotal / 500000) * 100, 100)}%` }}
                        />
                    </div>
                    <p className="text-[11px] text-slate-600 mt-1.5">
                        {translate("cart.free_shipping_amount", {
                            amount: formatCurrency(500000 - subtotal),
                        })}
                    </p>
                </div>
            )}

            <Button as={Link} to="/checkout" size="block" className="gap-2">
                <span>{translate("cart.checkout_button")}</span>
                <ArrowRight size={17} />
            </Button>

            <Link
                to="/shop"
                className="block text-center text-xs text-slate-500 hover:text-[#004687] font-semibold mt-3.5 transition-colors"
            >
                {translate("cart.continue_shopping")}
            </Link>

            <div className="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-center gap-1.5 text-[11px] text-slate-400">
                <ShieldCheck size={13} className="text-[#004687]" />
                <span>{translate("cart.encrypted_note") || "Encrypted checkout & verified fulfillment"}</span>
            </div>
        </Card>
    )
}

export default CartSummary
