import React, { useState } from "react"
import { Search, Truck, CheckCircle2, Clock } from "lucide-react"
import { Card, Button, IconBadge } from "../../../components/common"

const OrderTracking = () => {
    const [orderId, setOrderId] = useState("")
    const [trackingInfo, setTrackingInfo] = useState(null)

    const handleTrack = (e) => {
        e.preventDefault()
        // Simulate tracking
        setTrackingInfo({
            id: orderId || "#NS-9923",
            status: "In Transit",
            steps: [
                { label: "Đã đặt hàng", date: "20/04/2026", completed: true },
                { label: "Đang xử lý", date: "21/04/2026", completed: true },
                { label: "Đang giao hàng", date: "24/04/2026", completed: true },
                { label: "Hoàn thành", date: "Dự kiến 26/04", completed: false },
            ],
        })
    }

    return (
        <div className="space-y-8">
            <Card shadow="md" className="p-8 md:p-10">
                <Card.Header className="px-0 pt-0 border-none">
                    <Card.Title className="text-2xl font-black tracking-tight">Theo dõi đơn hàng</Card.Title>
                    <p className="text-slate-500 mt-2">Nhập mã đơn hàng của bạn để kiểm tra trạng thái vận chuyển.</p>
                </Card.Header>

                <Card.Body className="px-0 pb-0">
                    <form onSubmit={handleTrack} className="flex flex-col md:flex-row gap-4">
                        <div className="flex-1 relative">
                            <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <Search size={18} />
                            </span>
                            <input type="text" value={orderId} onChange={(e) => setOrderId(e.target.value)} placeholder="Nhập mã đơn hàng (Ví dụ: NS-9923)" className="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all font-medium" />
                        </div>
                        <Button type="submit" variant="blue" size="xl">Theo dõi ngay</Button>
                    </form>
                </Card.Body>
            </Card>

            {trackingInfo && (
                <Card shadow="md" className="p-8 md:p-10">
                    <Card.Body className="px-0 py-0">
                        <div className="flex items-center gap-4 mb-10 pb-6 border-b border-slate-100">
                            <IconBadge icon={Truck} color="blue" size="lg" />
                            <div>
                                <p className="text-sm text-slate-500 font-medium">Trạng thái đơn hàng {trackingInfo.id}</p>
                                <p className="text-xl font-black text-slate-900">Đang vận chuyển</p>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="absolute left-[27px] top-0 bottom-0 w-0.5 bg-slate-100"></div>

                            <div className="space-y-10 relative">
                                {trackingInfo.steps.map((step, index) => (
                                    <div key={index} className="flex items-start gap-6">
                                        <div className={`w-14 h-14 rounded-2xl flex items-center justify-center z-10 ${step.completed ? "bg-blue-600 text-white shadow-lg shadow-blue-100" : "bg-white border-2 border-slate-100 text-slate-300"}`}>{step.completed ? <CheckCircle2 size={24} /> : <Clock size={24} />}</div>
                                        <div className="pt-2">
                                            <p className={`font-bold ${step.completed ? "text-slate-900" : "text-slate-400"}`}>{step.label}</p>
                                            <p className="text-sm text-slate-500">{step.date}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </Card.Body>
                </Card>
            )}
        </div>
    )
}

export default OrderTracking
