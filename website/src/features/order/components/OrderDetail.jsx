import React from "react"
import { ArrowLeft, Package, Calendar, CreditCard, MapPin } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"
import Card from "../../../components/common/Card"

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

    const statusColors = {
        pending: "bg-yellow-100 text-yellow-700",
        processing: "bg-blue-100 text-blue-700",
        shipping: "bg-indigo-100 text-indigo-700",
        completed: "bg-green-100 text-green-700",
        cancelled: "bg-red-100 text-red-700",
        refunded: "bg-purple-100 text-purple-700",
    }
    const paymentStatusColors = {
        pending: "bg-yellow-100 text-yellow-700",
        paid: "bg-green-100 text-green-700",
        failed: "bg-red-100 text-red-700",
    }

    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-right duration-300">
            <div className="flex items-center justify-between mb-4">
                <button onClick={onBack} className="flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors font-bold">
                    <ArrowLeft size={20} />
                    Quay lại danh sách
                </button>
                <div className="flex items-center gap-2">
                    <span className={`px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${statusColors[order.status] || "bg-slate-100 text-slate-600"}`}>{order.status}</span>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2 space-y-8">
                    <Card className="shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
                        <Card.Header>
                            <div className="flex justify-between items-center">
                                <Card.Title>Chi tiết sản phẩm</Card.Title>
                                <span className="text-sm font-bold text-slate-500">Mã đơn: {order.number || order.id}</span>
                            </div>
                        </Card.Header>
                        <Card.Body>
                            <div className="space-y-6">
                                {items.map((item) => (
                                    <div key={item.id} className="flex gap-6 pb-6 border-b border-slate-100 last:border-0 last:pb-0">
                                        <div className="w-20 h-20 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0 flex justify-center items-center text-slate-400">{item.product?.image?.url ? <img src={item.product.image.url} alt={item.product?.name || "Product"} className="w-full h-full object-cover" /> : <Package size={32} />}</div>
                                        <div className="flex-1">
                                            <h4 className="font-bold text-slate-900">{item.product?.name || item.name || "Sản phẩm"}</h4>
                                            <p className="text-sm text-slate-500">Số lượng: {item.qty}</p>
                                            <p className="text-blue-600 font-black mt-1">{formatCurrency(item.unit_price)}</p>

                                            {/* Warehouse Info */}
                                            {item.metadata?.inventory_name && (
                                                <div className="flex items-center gap-1 text-xs text-slate-500 mt-1">
                                                    <MapPin size={12} />
                                                    <span>Xuất từ kho: {item.metadata.inventory_name}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </Card.Body>
                    </Card>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <Card className="shadow-sm border-slate-100">
                            <Card.Header className="p-6 border-b border-slate-50">
                                <div className="flex items-center gap-3 text-slate-900 font-bold">
                                    <CreditCard size={18} className="text-blue-600" />
                                    Địa chỉ thanh toán
                                </div>
                            </Card.Header>
                            <Card.Body className="p-6 text-sm font-medium text-slate-600 leading-relaxed">
                                {billingAddress?.first_name ? (
                                    <>
                                        <p className="font-bold text-slate-900 mb-2">
                                            {billingAddress.first_name} {billingAddress.last_name}
                                        </p>
                                        {billingAddress.email && <p className="mb-1">{billingAddress.email}</p>}
                                        {billingAddress.phone && <p className="mb-2">{billingAddress.phone}</p>}
                                        <p>{formatAddress(billingAddress)}</p>
                                    </>
                                ) : (
                                    <p className="text-slate-400">Giống địa chỉ giao hàng</p>
                                )}
                            </Card.Body>
                        </Card>

                        <Card className="shadow-sm border-slate-100">
                            <Card.Header className="p-6 border-b border-slate-50">
                                <div className="flex items-center gap-3 text-slate-900 font-bold">
                                    <MapPin size={18} className="text-blue-600" />
                                    Địa chỉ giao hàng
                                </div>
                            </Card.Header>
                            <Card.Body className="p-6 text-sm font-medium text-slate-600 leading-relaxed">
                                {shippingAddress?.first_name ? (
                                    <>
                                        <p className="font-bold text-slate-900 mb-2">
                                            {shippingAddress.first_name} {shippingAddress.last_name}
                                        </p>
                                        {shippingAddress.phone && <p className="mb-2">{shippingAddress.phone}</p>}
                                        <p>{formatAddress(shippingAddress)}</p>
                                    </>
                                ) : (
                                    <p className="text-slate-400">Không có địa chỉ giao hàng</p>
                                )}
                            </Card.Body>
                        </Card>
                    </div>

                    <Card className="shadow-sm border-slate-100">
                        <Card.Header className="p-6 border-b border-slate-50">
                            <div className="flex items-center gap-3 text-slate-900 font-bold">
                                <CreditCard size={18} className="text-blue-600" />
                                Chi tiết giá
                            </div>
                        </Card.Header>
                        <Card.Body className="p-6 space-y-4 text-sm font-medium">
                            <div className="flex justify-between text-slate-500">
                                <span>Tạm tính</span>
                                <span className="text-slate-900">{formatCurrency(order.subtotal || 0)}</span>
                            </div>

                            {order.discount_total > 0 && (
                                <div className="flex justify-between text-green-600">
                                    <span>Khuyến mãi {order.coupons?.length > 0 ? `(${order.coupons.map((c) => c.code).join(", ")})` : ""}</span>
                                    <span>-{formatCurrency(order.discount_total)}</span>
                                </div>
                            )}

                            {order.loyalty_discount > 0 && (
                                <div className="flex justify-between text-green-600">
                                    <span>Đổi điểm tích lũy</span>
                                    <span>-{formatCurrency(order.loyalty_discount)}</span>
                                </div>
                            )}

                            {order.tax_amount > 0 && (
                                <div className="flex justify-between text-slate-500">
                                    <span>Thuế</span>
                                    <span className="text-slate-900">{formatCurrency(order.tax_amount)}</span>
                                </div>
                            )}

                            <div className="flex justify-between text-slate-500">
                                <span>Vận chuyển {order.shipping_method ? `(${order.shipping_method})` : ""}</span>
                                <span className={order.shipping_cost > 0 ? "text-slate-900" : "text-green-600"}>{order.shipping_cost > 0 ? formatCurrency(order.shipping_cost) : "Miễn phí"}</span>
                            </div>

                            <div className="pt-4 border-t border-slate-100 flex justify-between items-center text-base">
                                <span className="font-bold text-slate-900">Tổng cộng</span>
                                <span className="font-black text-blue-600">{formatCurrency(order.total)}</span>
                            </div>
                        </Card.Body>
                    </Card>
                </div>

                <div className="space-y-8">
                    <Card className="shadow-sm border-slate-100">
                        <Card.Header className="p-6 border-b border-slate-50">
                            <div className="flex items-center gap-3 text-slate-900 font-bold">
                                <Calendar size={18} className="text-blue-600" />
                                Thông tin đơn hàng
                            </div>
                        </Card.Header>
                        <Card.Body className="p-6 space-y-4">
                            <div>
                                <p className="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-1">Ngày đặt</p>
                                <p className="font-bold text-slate-900">
                                    {new Date(order.created_at).toLocaleDateString("vi-VN")} - {new Date(order.created_at).toLocaleTimeString("vi-VN")}
                                </p>
                            </div>

                            {order.payment_method && (
                                <div>
                                    <p className="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-1">Phương thức thanh toán</p>
                                    <p className="font-bold text-slate-900 uppercase">{order.payment_method}</p>
                                </div>
                            )}

                            {order.payment_status && (
                                <div>
                                    <p className="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-1">Trạng thái thanh toán</p>
                                    <span className={`inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ${paymentStatusColors[order.payment_status] || "bg-slate-100 text-slate-600"}`}>{order.payment_status}</span>
                                </div>
                            )}

                            {order.customer_notes && (
                                <div>
                                    <p className="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-1">Ghi chú của bạn</p>
                                    <div className="bg-slate-50 p-3 rounded-lg text-sm text-slate-700 italic border border-slate-100">"{order.customer_notes}"</div>
                                </div>
                            )}
                        </Card.Body>
                    </Card>

                    {order.refunds && order.refunds.length > 0 && (
                        <Card className="shadow-sm border-slate-100">
                            <Card.Header className="p-6 border-b border-slate-50">
                                <div className="flex items-center gap-3 text-red-600 font-bold">
                                    <Package size={18} />
                                    Lịch sử hoàn tiền
                                </div>
                            </Card.Header>
                            <Card.Body className="p-6 space-y-4">
                                {order.refunds.map((refund, idx) => (
                                    <div key={idx} className="pb-4 border-b border-slate-100 last:border-0 last:pb-0">
                                        <div className="flex justify-between items-center mb-2">
                                            <span className="font-bold text-slate-900 text-sm">{refund.type === "full" ? "Hoàn toàn bộ" : "Hoàn 1 phần"}</span>
                                            <span className={`px-2 py-1 text-[10px] uppercase font-bold rounded-full ${refund.status === "completed" ? "bg-green-100 text-green-600" : "bg-orange-100 text-orange-600"}`}>{refund.status === "completed" ? "Thành công" : "Đang xử lý"}</span>
                                        </div>
                                        <p className="text-sm text-slate-500 mb-1">
                                            Số tiền: <span className="font-bold text-red-600">{formatCurrency(refund.amount)}</span>
                                        </p>
                                        {refund.reason && <p className="text-sm text-slate-500 mb-1 line-clamp-2">Lý do: {refund.reason}</p>}
                                        <p className="text-xs text-slate-400 mt-2">
                                            {new Date(refund.date).toLocaleDateString("vi-VN")} {new Date(refund.date).toLocaleTimeString("vi-VN")}
                                        </p>
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
