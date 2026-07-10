import React, { useState, useMemo } from "react"
import { Package } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"
import Card from "../../../components/common/Card"

const OrderHistory = ({ orders, onViewOrder }) => {
    const { formatCurrency } = useFormatters()
    const [statusFilter, setStatusFilter] = useState("all")
    const [timeFilter, setTimeFilter] = useState("all")

    const filteredOrders = useMemo(() => {
        return orders.filter((order) => {
            // Status filter
            if (statusFilter !== "all" && order.status !== statusFilter) return false

            // Time filter
            if (timeFilter !== "all") {
                const orderDate = new Date(order.created_at)
                const now = new Date()
                if (timeFilter === "30_days" && now - orderDate > 30 * 24 * 60 * 60 * 1000) return false
                if (timeFilter === "6_months" && now - orderDate > 6 * 30 * 24 * 60 * 60 * 1000) return false
                if (timeFilter === "1_year" && now - orderDate > 365 * 24 * 60 * 60 * 1000) return false
            }
            return true
        })
    }, [orders, statusFilter, timeFilter])

    return (
        <Card className="p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
            <Card.Header className="px-0 pt-0 border-none flex flex-col md:flex-row justify-between md:items-center gap-4">
                <Card.Title className="text-2xl font-black tracking-tight">Lịch sử đơn hàng</Card.Title>
                <div className="flex items-center gap-4">
                    <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className="p-2 border border-slate-200 rounded-lg text-sm bg-slate-50">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="shipping">Đang giao</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>

                    <select value={timeFilter} onChange={(e) => setTimeFilter(e.target.value)} className="p-2 border border-slate-200 rounded-lg text-sm bg-slate-50">
                        <option value="all">Mọi thời gian</option>
                        <option value="30_days">30 ngày qua</option>
                        <option value="6_months">6 tháng qua</option>
                        <option value="1_year">Năm qua</option>
                    </select>
                </div>
            </Card.Header>

            <Card.Body className="px-0 pb-0">
                <div className="space-y-4">
                    {filteredOrders.map((order) => {
                        const firstItem = order.items && order.items.length > 0 ? order.items[0] : null
                        const extraItemsCount = order.items ? order.items.length - 1 : 0
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
                            <div key={order.id} onClick={() => onViewOrder(order)} className="group p-6 border border-slate-100 rounded-3xl hover:border-blue-200 hover:bg-blue-50/20 transition-all cursor-pointer flex flex-col md:flex-row justify-between md:items-center gap-4">
                                <div className="flex items-start md:items-center gap-4 md:gap-6 flex-1">
                                    <div className="w-16 h-16 md:w-20 md:h-20 bg-slate-100 rounded-2xl overflow-hidden shadow-sm flex-shrink-0 flex items-center justify-center text-slate-400">{firstItem?.product?.image?.url ? <img src={firstItem.product.image.url} alt={firstItem.product.name} className="w-full h-full object-cover" /> : <Package size={24} />}</div>
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2 mb-1">
                                            <p className="font-bold text-slate-900">Mã đơn: {order.number || order.id}</p>
                                            <span className={`text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full ${statusColors[order.status] || "bg-slate-100 text-slate-600"}`}>{order.status}</span>
                                            {order.payment_status && <span className={`text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full ${paymentStatusColors[order.payment_status] || "bg-slate-100 text-slate-600"}`}>{order.payment_status}</span>}
                                        </div>
                                        <p className="text-xs text-slate-500 mb-2">Ngày đặt: {new Date(order.created_at).toLocaleDateString("vi-VN")}</p>
                                        {firstItem && (
                                            <p className="text-sm font-medium text-slate-800 line-clamp-1">
                                                {firstItem.product?.name || "Sản phẩm"} (x{firstItem.qty}){extraItemsCount > 0 && <span className="text-slate-500 ml-1">+ {extraItemsCount} sản phẩm khác</span>}
                                            </p>
                                        )}
                                        {order.payment_method && <p className="text-xs text-slate-500 mt-1 uppercase">Thanh toán qua: {order.payment_method}</p>}
                                    </div>
                                </div>

                                <div className="text-left md:text-right flex flex-row md:flex-col justify-between items-center md:items-end">
                                    <p className="text-xs text-slate-500 uppercase tracking-widest font-bold mb-1">Tổng tiền</p>
                                    <p className="font-black text-blue-600 text-lg md:text-xl">{formatCurrency(order.total)}</p>
                                    <button className="mt-2 text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">Xem chi tiết</button>
                                </div>
                            </div>
                        )
                    })}
                </div>

                {filteredOrders.length === 0 && (
                    <div className="py-20 flex flex-col items-center justify-center text-center">
                        <div className="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                            <Package size={32} />
                        </div>
                        <p className="text-slate-900 font-bold mb-1">Bạn chưa có đơn hàng nào phù hợp</p>
                        <p className="text-slate-500 text-sm">Hãy thử thay đổi bộ lọc hoặc tiếp tục mua sắm.</p>
                    </div>
                )}
            </Card.Body>
        </Card>
    )
}

export default OrderHistory
