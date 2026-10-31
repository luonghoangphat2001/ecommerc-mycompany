import React, { useState } from "react"
import { Search, Truck, CheckCircle2, Clock } from "lucide-react"
import { Card, Button, IconBadge, FormInput } from "../../../components/common"

const OrderTracking = () => {
    const [orderId, setOrderId] = useState("")
    const [trackingInfo, setTrackingInfo] = useState(null)

    const handleTrack = (e) => {
        e.preventDefault()
        setTrackingInfo({
            id: orderId || "#NS-9923",
            status: "In Transit",
            steps: [
                { label: "Order Confirmed", date: "20/04/2026", completed: true },
                { label: "Warehouse Packed", date: "21/04/2026", completed: true },
                { label: "In Transit to Destination", date: "24/04/2026", completed: true },
                { label: "Delivered", date: "Est. 26/04/2026", completed: false },
            ],
        })
    }

    return (
        <div className="space-y-6 tabular-nums">
            <Card className="p-6 sm:p-8">
                <div className="pb-4 mb-5 border-b border-slate-100">
                    <h2 className="text-lg font-bold text-[#09253E] tracking-tight">Order Shipment Tracking</h2>
                    <p className="text-xs text-slate-500 mt-0.5">
                        Enter your order reference number to inspect real-time dispatch milestones.
                    </p>
                </div>

                <form onSubmit={handleTrack} className="flex flex-col sm:flex-row gap-3">
                    <FormInput
                        name="order-tracking-id"
                        icon={Search}
                        value={orderId}
                        onChange={(e) => setOrderId(e.target.value)}
                        placeholder="Enter Order ID (e.g. NS-9923)"
                        className="flex-1"
                    />
                    <Button type="submit" variant="primary" size="md" className="shrink-0">
                        Track Shipment
                    </Button>
                </form>
            </Card>

            {trackingInfo && (
                <Card className="p-6 sm:p-8">
                    <div className="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
                        <IconBadge icon={Truck} color="blue" size="md" />
                        <div>
                            <p className="text-xs text-slate-500 font-medium">
                                Shipment Reference <span className="font-mono font-bold text-slate-800">{trackingInfo.id}</span>
                            </p>
                            <p className="text-base font-bold text-[#09253E]">{trackingInfo.status}</p>
                        </div>
                    </div>

                    <div className="relative pl-2">
                        <div className="space-y-6">
                            {trackingInfo.steps.map((step, index) => (
                                <div key={index} className="flex items-start gap-4">
                                    <div
                                        className={`w-9 h-9 rounded-lg flex items-center justify-center shrink-0 ${
                                            step.completed
                                                ? "bg-[#004687] text-white"
                                                : "bg-slate-100 border border-slate-200 text-slate-400"
                                        }`}
                                    >
                                        {step.completed ? <CheckCircle2 size={18} /> : <Clock size={18} />}
                                    </div>
                                    <div className="pt-0.5">
                                        <p
                                            className={`text-sm font-bold ${
                                                step.completed ? "text-[#09253E]" : "text-slate-400"
                                            }`}
                                        >
                                            {step.label}
                                        </p>
                                        <p className="text-xs text-slate-500">{step.date}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </Card>
            )}
        </div>
    )
}

export default OrderTracking
