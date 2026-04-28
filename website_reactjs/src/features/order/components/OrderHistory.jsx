import React from "react"
import { Package } from "lucide-react"
import { useFormatters } from "../../../utils/useFormatters"
import Card from "../../../components/common/Card"

const OrderHistory = ({ orders, onViewOrder }) => {
    const { formatCurrency } = useFormatters()
    return (
        <Card className="p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
            <Card.Header className="px-0 pt-0 border-none">
                <Card.Title className="text-2xl font-black tracking-tight">Lịch sử đơn hàng</Card.Title>
            </Card.Header>

            <Card.Body className="px-0 pb-0">
                <div className="space-y-4">
                    {orders.map((order) => (
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
                                <span className={`text-[10px] uppercase tracking-wider font-bold px-2 py-1 rounded-full ${order.status === "Đang xử lý" ? "bg-orange-100 text-orange-600" : "bg-green-100 text-green-600"}`}>{order.status}</span>
                            </div>
                        </div>
                    ))}
                </div>

                {orders.length === 0 && (
                    <div className="py-20 text-center">
                        <p className="text-slate-500">Bạn chưa có đơn hàng nào.</p>
                    </div>
                )}
            </Card.Body>
        </Card>
    )
}

export default OrderHistory
