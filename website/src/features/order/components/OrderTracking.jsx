import React, { useState } from "react"
import { Search, Truck, CheckCircle2, Clock, AlertCircle, Package } from "lucide-react"
import { Card, Button, IconBadge, FormInput, Alert } from "../../../components/common"
import orderService from "../services/orderService"
import { unwrapApiObject } from "../../../api/apiResponse"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"
import { formatOrderDate } from "../../../utils/date"

const buildTrackingSteps = (order, translate) => {
    const status = String(order?.status || "pending").toLowerCase()
    const createdDate = formatOrderDate(order?.created_at)
    const updatedDate = formatOrderDate(order?.updated_at || order?.created_at)

    const isPacked = ["processing", "shipping", "delivering", "shipped", "completed"].includes(status)
    const isTransit = ["shipping", "delivering", "shipped", "completed"].includes(status)
    const isDelivered = status === "completed"

    return [
        {
            label: translate("order.step_confirmed") || "Order Confirmed",
            date: createdDate,
            completed: status !== "cancelled",
        },
        {
            label: translate("order.step_packed") || "Warehouse Packed",
            date: isPacked ? updatedDate : "-",
            completed: isPacked,
        },
        {
            label: translate("order.step_transit") || "In Transit to Destination",
            date: isTransit ? updatedDate : "-",
            completed: isTransit,
        },
        {
            label: translate("order.step_delivered") || "Delivered",
            date: isDelivered ? updatedDate : "-",
            completed: isDelivered,
        },
    ]
}

const OrderTracking = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency, getLocalizedText } = useFormatters()
    const [orderId, setOrderId] = useState("")
    const [trackedOrder, setTrackedOrder] = useState(null)
    const [loading, setLoading] = useState(false)
    const [error, setError] = useState("")

    const handleTrack = async (e) => {
        e.preventDefault()
        const cleanCode = orderId.trim()
        if (!cleanCode) return

        setLoading(true)
        setError("")
        setTrackedOrder(null)

        try {
            const response = await orderService.trackOrder(cleanCode)
            const orderData = unwrapApiObject(response)
            if (orderData && (orderData.id || orderData.number)) {
                setTrackedOrder(orderData)
            } else {
                setError(translate("order.tracking_not_found") || "Order not found with the provided reference.")
            }
        } catch (err) {
            setError(
                err?.response?.data?.message ||
                    translate("order.tracking_not_found") ||
                    "Order not found with the provided reference."
            )
        } finally {
            setLoading(false)
        }
    }

    const steps = trackedOrder ? buildTrackingSteps(trackedOrder, translate) : []
    const items = trackedOrder?.items || []

    return (
        <div className="space-y-6 tabular-nums">
            <Card className="p-6 sm:p-8">
                <div className="pb-4 mb-5 border-b border-slate-100">
                    <h2 className="text-lg font-bold text-[#09253E] tracking-tight">
                        {translate("order.tracking_title") || "Order Shipment Tracking"}
                    </h2>
                    <p className="text-xs text-slate-500 mt-0.5">
                        {translate("order.tracking_subtitle") ||
                            "Enter your order reference number to inspect real-time dispatch milestones."}
                    </p>
                </div>

                <form onSubmit={handleTrack} className="flex flex-col sm:flex-row gap-3">
                    <FormInput
                        name="order-tracking-id"
                        icon={Search}
                        value={orderId}
                        onChange={(e) => setOrderId(e.target.value)}
                        placeholder={translate("order.tracking_placeholder") || "Enter Order Number (e.g. ORD-...)"}
                        className="flex-1"
                        required
                    />
                    <Button type="submit" variant="primary" size="md" disabled={loading} className="shrink-0">
                        {loading
                            ? translate("common.loading") || "Loading..."
                            : translate("order.track_button") || "Track Shipment"}
                    </Button>
                </form>

                {error && (
                    <Alert variant="error" className="mt-4">
                        <div className="flex items-center gap-2">
                            <AlertCircle size={15} />
                            <span>{error}</span>
                        </div>
                    </Alert>
                )}
            </Card>

            {trackedOrder && (
                <Card className="p-6 sm:p-8 space-y-6">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div className="flex items-center gap-3.5">
                            <IconBadge icon={Truck} color="blue" size="md" />
                            <div>
                                <p className="text-xs text-slate-500 font-medium">
                                    {translate("order.shipment_reference") || "Shipment Reference"}{" "}
                                    <span className="font-mono font-bold text-slate-800">
                                        #{trackedOrder.number || trackedOrder.id}
                                    </span>
                                </p>
                                <p className="text-base font-bold text-[#09253E]">
                                    {translate(`order.${trackedOrder.status}`) || trackedOrder.status}
                                </p>
                            </div>
                        </div>

                        {trackedOrder.total !== undefined && (
                            <div className="text-left sm:text-right">
                                <p className="text-xs text-slate-400 uppercase font-semibold">
                                    {translate("order.total") || "Total"}
                                </p>
                                <p className="text-base font-extrabold text-[#004687]">
                                    {formatCurrency(trackedOrder.total)}
                                </p>
                            </div>
                        )}
                    </div>

                    <div className="relative pl-2">
                        <div className="space-y-6">
                            {steps.map((step, index) => (
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

                    {items.length > 0 && (
                        <div className="pt-5 border-t border-slate-100">
                            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">
                                {translate("order.line_items") || "Order Line Items"}
                            </h3>
                            <div className="divide-y divide-slate-100">
                                {items.map((item) => (
                                    <div key={item.id} className="py-2.5 flex items-center justify-between gap-4 text-xs">
                                        <div className="flex items-center gap-2.5 min-w-0">
                                            <Package size={15} className="text-[#004687] shrink-0" />
                                            <span className="font-semibold text-slate-800 truncate">
                                                {getLocalizedText(item.product?.name || item.name)}
                                            </span>
                                            <span className="text-slate-500">×{item.qty || item.quantity}</span>
                                        </div>
                                        {item.unit_price !== undefined && (
                                            <span className="font-bold text-slate-900">
                                                {formatCurrency(item.unit_price * (item.qty || item.quantity || 1))}
                                            </span>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </Card>
            )}
        </div>
    )
}

export default OrderTracking
