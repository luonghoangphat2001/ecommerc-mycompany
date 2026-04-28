import React from "react"
import { Link } from "react-router-dom"
import { ArrowRight } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"
import { Card, Button } from "../../../components/common"

const CartSummary = ({ total, translate }) => {
    const { formatCurrency } = useFormatters()
    return (
        <Card shadow="md" className="p-8 sticky top-32">
            <h2 className="text-xl font-bold text-slate-900 mb-6">{translate("cart.summary_title")}</h2>

            <div className="space-y-4 mb-8">
                <div className="flex justify-between text-slate-500 font-medium">
                    <span>{translate("cart.subtotal")}</span>
                    <span>{formatCurrency(total)}</span>
                </div>
                <div className="flex justify-between text-slate-500 font-medium">
                    <span>{translate("cart.shipping")}</span>
                    <span className="text-blue-600">{translate("cart.free")}</span>
                </div>
                <div className="flex justify-between text-slate-900 font-bold text-lg pt-4 border-t border-slate-200">
                    <span>{translate("cart.total")}</span>
                    <span>{formatCurrency(total)}</span>
                </div>
            </div>

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
