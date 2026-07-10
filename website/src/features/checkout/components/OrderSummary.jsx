import React from "react"
import { ShoppingBag } from "lucide-react"
import { Card, Button } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"

const OrderSummary = React.memo(({ items, summary = {}, isSubmitting }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()
    
    const subtotal = summary.subtotal || 0;
    const shippingCost = summary.shipping?.amount || 0;
    const taxAmount = summary.tax?.amount || 0;
    const discountAmount = summary.discount?.amount || 0;
    const grandTotal = summary.total || 0;

    return (
        <div className="lg:col-span-1">
            <Card shadow="md" className="sticky top-32">
                <Card.Header>
                    <Card.Title>{translate("cart.summary_title")}</Card.Title>
                </Card.Header>

                <Card.Body>
                    {/* Items list */}
                    <div className="max-h-60 overflow-y-auto mb-6 pr-2 space-y-4 custom-scrollbar">
                        {items.map((item) => (
                            <div key={item.id} className="flex gap-4">
                                <div className="w-14 h-14 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0">
                                    <img
                                        src={item.image?.url || item.image || item.image_url || "/placeholder-product.jpg"}
                                        alt={item.name}
                                        className="w-full h-full object-cover"
                                        onError={(e) => {
                                            e.target.src = "/placeholder-product.jpg"
                                        }}
                                    />
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-bold text-slate-800 truncate">{item.name}</p>
                                    <p className="text-xs text-slate-500">x{item.quantity}</p>
                                </div>
                                <p className="text-sm font-bold text-slate-900">{formatCurrency(item.price * item.quantity)}</p>
                            </div>
                        ))}
                    </div>

                    {/* Summary */}
                    <div className="space-y-4 pt-6 border-t border-slate-100">
                        <div className="flex justify-between text-slate-500 font-medium">
                            <span>{translate("cart.subtotal") || "Tạm tính"}</span>
                            <span>{formatCurrency(subtotal)}</span>
                        </div>
                        
                        {discountAmount > 0 && (
                            <div className="flex justify-between text-green-600 font-medium">
                                <span>{translate("cart.discount") || "Giảm giá"}</span>
                                <span>-{formatCurrency(discountAmount)}</span>
                            </div>
                        )}
                        
                        <div className="flex justify-between text-slate-500 font-medium">
                            <span>{translate("cart.shipping")}</span>
                            <span className={shippingCost === 0 ? "text-green-600" : ""}>{shippingCost > 0 ? formatCurrency(shippingCost) : translate("cart.free")}</span>
                        </div>
                        
                        {taxAmount > 0 && (
                            <div className="flex justify-between text-slate-500 font-medium">
                                <span>{translate("cart.tax") || "Thuế"}</span>
                                <span>{formatCurrency(taxAmount)}</span>
                            </div>
                        )}
                        
                        <div className="flex justify-between pt-4 border-t border-slate-100 items-center">
                            <span className="text-lg font-bold text-slate-900">{translate("cart.total")}</span>
                            <span className="text-2xl font-black text-blue-600">{formatCurrency(grandTotal)}</span>
                        </div>
                    </div>
                </Card.Body>

                <Card.Footer>
                    <Button type="submit" variant="blue" size="block" disabled={isSubmitting} className="gap-2 hover:-translate-y-0.5 hover:shadow-blue-200">
                        <ShoppingBag size={20} />
                        {isSubmitting ? translate("checkout.order_processing") : translate("checkout.place_order")}
                    </Button>
                </Card.Footer>
            </Card>
        </div>
    )
})

export default OrderSummary
