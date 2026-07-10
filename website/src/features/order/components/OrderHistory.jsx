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
                    {filteredOrders.map((order) => (
                        <div key={order.id} onClick={() => onViewOrder(order)} className="group p-6 border border-slate-100 rounded-3xl hover:border-blue-200 hover:bg-blue-50/20 transition-all cursor-pointer flex items-center justify-between">
                            <div className="flex items-center gap-6">
                                <div className="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-slate-400 group-hover:text-blue-500 transition-colors">
                                    <Package size={24} />
                                </div>
                                <div>
                                    <p className="font-bold text-slate-900">{order.number || order.id}</p>
                                    <p className="text-xs text-slate-500">{new Date(order.created_at).toLocaleDateString("vi-VN")}</p>
                                </div>
                            </div>

                            <div className="text-right">
                                <p className="font-bold text-slate-900">{formatCurrency(order.total)}</p>
                                <span className={`text-[10px] uppercase tracking-wider font-bold px-2 py-1 rounded-full ${order.status === "completed" ? "bg-green-100 text-green-600" : "bg-orange-100 text-orange-600"}`}>{order.status}</span>
                            </div>
                        </div>
                    ))}
                </div>

                {filteredOrders.length === 0 && (
                    <div className="py-20 text-center">
                        <p className="text-slate-500">Bạn chưa có đơn hàng nào phù hợp.</p>
                    </div>
                )}
            </Card.Body>
        </Card>
    )
}

export default OrderHistory
