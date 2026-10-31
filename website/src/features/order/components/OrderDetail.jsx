import React from "react"
import { ArrowLeft, Package, Calendar, CreditCard, MapPin } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"
import Card from "../../../components/common/Card"

const STATUS_BADGE = {
    pending: "bg-amber-50 text-amber-800 border-amber-200",
    new: "bg-sky-50 text-sky-800 border-sky-200",
    processing: "bg-sky-50 text-[#004687] border-sky-200",
    shipping: "bg-sky-50 text-sky-800 border-sky-200",
    completed: "bg-emerald-50 text-emerald-700 border-emerald-200",
    cancelled: "bg-red-50 text-red-700 border-red-200",
    refunded: "bg-slate-100 text-slate-700 border-slate-300",
}

const PAYMENT_BADGE = {
    pending: "bg-amber-50 text-amber-800 border-amber-200",
    paid: "bg-emerald-50 text-emerald-700 border-emerald-200",
    failed: "bg-red-50 text-red-700 border-red-200",
}

const OrderDetail = ({ order, onBack }) => {
    const { formatCurrency } = useFormatters()
    if (!order) return null

    const items = order.items || []
    const shippingAddress = order.shipping_address || {}
    const billingAddress = order.billing_address || {}

    const formatAddress = (addr) => {
        const parts = []
        if (addr.street) parts.push(addr.street)
        if (addr.ward) parts.push(addr.ward)
        if (addr.state) parts.push(addr.state)
        if (addr.city) parts.push(addr.city)
        if (addr.country) parts.push(addr.country)
        return parts.join(", ")
    }

    return (
        <div className="space-y-6 tabular-nums animate-fade-up">
            <div className="flex items-center justify-between bg-white border border-slate-200/90 rounded-xl px-5 py-3.5 shadow-card">
                <button
                    type="button"
                    onClick={onBack}
                    className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-[#004687] transition-colors"
                >
                    <ArrowLeft size={16} />
                    <span>Back to Orders</span>
                </button>
                <div className="flex items-center gap-2">
                    <span className="font-mono text-xs font-bold text-[#09253E]">
                        #{order.number || order.id}
                    </span>
                    <span
                        className={`px-2.5 py-0.5 rounded border text-[11px] font-bold uppercase tracking-wider ${
                            STATUS_BADGE[order.status] || "bg-slate-100 text-slate-600 border-slate-200"
                        }`}
                    >
                        {order.status}
                    </span>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    {/* Line Items Card */}
                    <Card>
                        <Card.Header>
                            <div className="flex justify-between items-center">
                                <Card.Title>Order Line Items</Card.Title>
                                <span className="text-xs font-semibold text-slate-500">
                                    {items.length} item(s)
                                </span>
                            </div>
                        </Card.Header>
                        <Card.Body>
                            <div className="divide-y divide-slate-100">
                                {items.map((item) => (
                                    <div key={item.id} className="flex gap-4 py-4 first:pt-0 last:pb-0">
                                        <div className="w-16 h-16 bg-slate-100 rounded-lg border border-slate-200/70 overflow-hidden flex-shrink-0 flex justify-center items-center text-slate-400">
                                            {item.product?.image?.url ? (
                                                <img
                                                    src={item.product.image.url}
                                                    alt={item.product?.name || "Product"}
                                                    className="w-full h-full object-cover"
                                                />
                                            ) : (
                                                <Package size={24} />
                                            )}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <h4 className="font-bold text-sm text-[#09253E]">
                                                {item.product?.name || item.name || "Product"}
                                            </h4>
                                            <p className="text-xs text-slate-500 mt-0.5">Quantity: {item.qty}</p>
                                            <p className="text-[#004687] font-bold text-sm mt-1">
                                                {formatCurrency(item.unit_price)}
                                            </p>

                                            {item.metadata?.inventory_name && (
                                                <div className="flex items-center gap-1 text-xs text-slate-500 mt-1">
                                                    <MapPin size={12} />
                                                    <span>Dispatched from: {item.metadata.inventory_name}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </Card.Body>
                    </Card>

                    {/* Addresses Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card>
                            <Card.Header>
                                <div className="flex items-center gap-2 text-[#09253E] font-bold text-sm">
                                    <CreditCard size={16} className="text-[#004687]" />
                                    <span>Billing Address</span>
                                </div>
                            </Card.Header>
                            <Card.Body className="text-xs text-slate-600 leading-relaxed space-y-1">
                                {billingAddress?.first_name ? (
                                    <>
                                        <p className="font-bold text-slate-900 text-sm">
                                            {billingAddress.first_name} {billingAddress.last_name}
                                        </p>
                                        {billingAddress.email && <p>{billingAddress.email}</p>}
                                        {billingAddress.phone && <p>{billingAddress.phone}</p>}
                                        <p>{formatAddress(billingAddress)}</p>
                                    </>
                                ) : (
                                    <p className="text-slate-400">Same as shipping address</p>
                                )}
                            </Card.Body>
                        </Card>

                        <Card>
                            <Card.Header>
                                <div className="flex items-center gap-2 text-[#09253E] font-bold text-sm">
                                    <MapPin size={16} className="text-[#004687]" />
                                    <span>Shipping Address</span>
                                </div>
                            </Card.Header>
                            <Card.Body className="text-xs text-slate-600 leading-relaxed space-y-1">
                                {shippingAddress?.first_name ? (
                                    <>
                                        <p className="font-bold text-slate-900 text-sm">
                                            {shippingAddress.first_name} {shippingAddress.last_name}
                                        </p>
                                        {shippingAddress.phone && <p>{shippingAddress.phone}</p>}
                                        <p>{formatAddress(shippingAddress)}</p>
                                    </>
                                ) : (
                                    <p className="text-slate-400">No shipping address provided</p>
                                )}
                            </Card.Body>
                        </Card>
                    </div>

                    {/* Financial Summary */}
                    <Card>
                        <Card.Header>
                            <div className="flex items-center gap-2 text-[#09253E] font-bold text-sm">
                                <CreditCard size={16} className="text-[#004687]" />
                                <span>Financial Summary</span>
                            </div>
                        </Card.Header>
                        <Card.Body className="space-y-3 text-sm">
                            <div className="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span className="font-semibold text-slate-900">
                                    {formatCurrency(order.subtotal || 0)}
                                </span>
                            </div>

                            {order.discount_total > 0 && (
                                <div className="flex justify-between text-emerald-700">
                                    <span>
                                        Discount{" "}
                                        {order.coupons?.length > 0
                                            ? `(${order.coupons.map((c) => c.code).join(", ")})`
                                            : ""}
                                    </span>
                                    <span className="font-semibold">-{formatCurrency(order.discount_total)}</span>
                                </div>
                            )}

                            {order.loyalty_discount > 0 && (
                                <div className="flex justify-between text-emerald-700">
                                    <span>Loyalty Redemption</span>
                                    <span className="font-semibold">-{formatCurrency(order.loyalty_discount)}</span>
                                </div>
                            )}

                            {order.tax_amount > 0 && (
                                <div className="flex justify-between text-slate-600">
                                    <span>Tax</span>
                                    <span className="font-semibold text-slate-900">
                                        {formatCurrency(order.tax_amount)}
                                    </span>
                                </div>
                            )}

                            <div className="flex justify-between text-slate-600">
                                <span>
                                    Shipping {order.shipping_method ? `(${order.shipping_method})` : ""}
                                </span>
                                <span
                                    className={`font-semibold ${
                                        order.shipping_cost > 0 ? "text-slate-900" : "text-emerald-700"
                                    }`}
                                >
                                    {order.shipping_cost > 0 ? formatCurrency(order.shipping_cost) : "Free"}
                                </span>
                            </div>

                            <div className="pt-3.5 border-t border-slate-200 flex justify-between items-baseline">
                                <span className="font-bold text-[#09253E] text-base">Total</span>
                                <span className="font-extrabold text-[#004687] text-lg">
                                    {formatCurrency(order.total)}
                                </span>
                            </div>
                        </Card.Body>
                    </Card>
                </div>

                {/* Right Metadata Column */}
                <div className="space-y-6">
                    <Card>
                        <Card.Header>
                            <div className="flex items-center gap-2 text-[#09253E] font-bold text-sm">
                                <Calendar size={16} className="text-[#004687]" />
                                <span>Order Metadata</span>
                            </div>
                        </Card.Header>
                        <Card.Body className="space-y-4 text-xs">
                            <div>
                                <p className="uppercase font-semibold text-slate-400 tracking-wider mb-1">
                                    Placed At
                                </p>
                                <p className="font-semibold text-slate-900">
                                    {new Date(order.created_at).toLocaleDateString("vi-VN")}{" "}
                                    {new Date(order.created_at).toLocaleTimeString("vi-VN")}
                                </p>
                            </div>

                            {order.payment_method && (
                                <div>
                                    <p className="uppercase font-semibold text-slate-400 tracking-wider mb-1">
                                        Payment Method
                                    </p>
                                    <p className="font-semibold text-slate-900 uppercase">
                                        {order.payment_method}
                                    </p>
                                </div>
                            )}

                            {order.payment_status && (
                                <div>
                                    <p className="uppercase font-semibold text-slate-400 tracking-wider mb-1">
                                        Payment Status
                                    </p>
                                    <span
                                        className={`inline-block px-2.5 py-0.5 rounded border text-[11px] font-bold uppercase ${
                                            PAYMENT_BADGE[order.payment_status] ||
                                            "bg-slate-100 text-slate-600 border-slate-200"
                                        }`}
                                    >
                                        {order.payment_status}
                                    </span>
                                </div>
                            )}

                            {order.customer_notes && (
                                <div>
                                    <p className="uppercase font-semibold text-slate-400 tracking-wider mb-1">
                                        Delivery Notes
                                    </p>
                                    <div className="bg-slate-50 p-3 rounded-lg text-xs text-slate-700 border border-slate-200/80">
                                        {order.customer_notes}
                                    </div>
                                </div>
                            )}
                        </Card.Body>
                    </Card>

                    {order.refunds && order.refunds.length > 0 && (
                        <Card>
                            <Card.Header>
                                <div className="flex items-center gap-2 text-red-600 font-bold text-sm">
                                    <Package size={16} />
                                    <span>Refund Ledger</span>
                                </div>
                            </Card.Header>
                            <Card.Body className="space-y-3 text-xs">
                                {order.refunds.map((refund, idx) => (
                                    <div
                                        key={idx}
                                        className="pb-3 border-b border-slate-100 last:border-0 last:pb-0"
                                    >
                                        <div className="flex justify-between items-center mb-1.5">
                                            <span className="font-bold text-slate-900">
                                                {refund.type === "full" ? "Full Refund" : "Partial Refund"}
                                            </span>
                                            <span
                                                className={`px-2 py-0.5 text-[10px] uppercase font-bold rounded border ${
                                                    refund.status === "completed"
                                                        ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                                                        : "bg-amber-50 text-amber-800 border-amber-200"
                                                }`}
                                            >
                                                {refund.status}
                                            </span>
                                        </div>
                                        <p className="text-slate-600">
                                            Amount:{" "}
                                            <span className="font-bold text-red-600">
                                                {formatCurrency(refund.amount)}
                                            </span>
                                        </p>
                                        {refund.reason && (
                                            <p className="text-slate-500 mt-1 line-clamp-2">
                                                Reason: {refund.reason}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </Card.Body>
                        </Card>
                    )}
                </div>
            </div>
        </div>
    )
}

export default OrderDetail
