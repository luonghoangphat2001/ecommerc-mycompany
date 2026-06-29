import React from "react"
import { Link } from "react-router-dom"
import { ArrowRight, Truck, Receipt, Percent, Tag } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"
import { Card, Button } from "../../../components/common"

const CartSummary = ({ summary, coupon, translate }) => {
    const { formatCurrency } = useFormatters()
    
    if (!summary) {
        summary = { subtotal: 0, shipping: { amount: 0 }, tax: { amount: 0 }, total: 0 }
    }
    
    const { subtotal, shipping, tax, total, discount = 0 } = summary
    const hasShipping = shipping?.amount > 0
    const hasTax = tax?.amount > 0
    const hasDiscount = discount > 0 || coupon
    
    return (
        <Card shadow="md" className="p-8 sticky top-32">
            <h2 className="text-xl font-bold text-slate-900 mb-6">{translate("cart.summary_title")}</h2>

            <div className="space-y-4 mb-6">
                {/* Subtotal */}
                <div className="flex justify-between text-slate-600">
                    <span>{translate("cart.subtotal")}</span>
                    <span className="font-medium">{formatCurrency(subtotal)}</span>
                </div>
                
                {/* Shipping */}
                <div className="flex justify-between items-center text-slate-600">
                    <div className="flex items-center gap-2">
                        <Truck size={16} />
                        <span>{shipping?.method_name || translate("cart.shipping")}</span>
                    </div>
                    <span className={`font-medium ${hasShipping ? '' : 'text-green-600'}`}>
                        {hasShipping ? formatCurrency(shipping.amount) : translate("cart.free")}
                    </span>
                </div>
                
                {/* Tax */}
                {hasTax && (
                    <div className="flex justify-between items-center text-slate-600">
                        <div className="flex items-center gap-2">
                            <Receipt size={16} />
                            <span>{tax?.rate_name || translate("cart.tax")}</span>
                        </div>
                        <span className="font-medium">{formatCurrency(tax.amount)}</span>
                    </div>
                )}
                
                {/* Discount / Coupon */}
                {hasDiscount && (
                    <div className="flex justify-between items-center text-green-600">
                        <div className="flex items-center gap-2">
                            <Tag size={16} />
                            <span>{coupon?.code || translate("cart.discount")}</span>
                        </div>
                        <span className="font-medium">-{formatCurrency(discount || coupon?.discount_amount || 0)}</span>
                    </div>
                )}
                
                {/* Items Count */}
                <div className="flex justify-between text-slate-500 text-sm">
                    <span>{translate("cart.items_count")}</span>
                    <span>{summary?.items_count || 0} {translate("cart.items")}</span>
                </div>
                
                {/* Total */}
                <div className="flex justify-between text-slate-900 font-bold text-xl pt-4 border-t-2 border-slate-200">
                    <span>{translate("cart.total")}</span>
                    <span className="text-blue-600">{formatCurrency(total)}</span>
                </div>
            </div>

            {/* Free Shipping Progress */}
            {subtotal < 500000 && (
                <div className="mb-6 p-4 bg-blue-50 rounded-xl">
                    <div className="flex items-center gap-2 text-blue-700 mb-2">
                        <Truck size={18} />
                        <span className="font-medium text-sm">{translate("cart.free_shipping_hint")}</span>
                    </div>
                    <div className="w-full bg-blue-200 rounded-full h-2">
                        <div 
                            className="bg-blue-600 h-2 rounded-full transition-all"
                            style={{ width: `${Math.min((subtotal / 500000) * 100, 100)}%` }}
                        />
                    </div>
                    <p className="text-xs text-blue-600 mt-2">
                        {translate("cart.free_shipping_amount", { amount: formatCurrency(500000 - subtotal) })}
                    </p>
                </div>
            )}

            <Button as={Link} to="/checkout" size="block" className="flex items-center justify-center gap-2 hover:-translate-y-0.5">
                {translate("cart.checkout_button")}
                <ArrowRight size={20} />
            </Button>

            <Link to="/shop" className="block text-center text-slate-500 hover:text-blue-600 font-medium mt-4 transition-colors">
                {translate("cart.continue_shopping")}
            </Link>
        </Card>
    )
}

export default CartSummary
