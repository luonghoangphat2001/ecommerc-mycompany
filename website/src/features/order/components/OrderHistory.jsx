import React, { useState, useMemo } from "react"
import { Package, ChevronRight, XCircle } from "lucide-react"
import toast from "react-hot-toast"
import { useFormatters } from "../../../utils/useFormatters"
import useSettingsStore from "../../../store/useSettingsStore"
import orderService from "../services/orderService"
import Card from "../../../components/common/Card"

const STATUS_BADGE = {
    pending: "bg-amber-50 text-amber-800 border-amber-200",
    new: "bg-sky-50 text-sky-800 border-sky-200",
    processing: "bg-sky-50 text-[#004687] border-sky-200",
    shipping: "bg-sky-50 text-sky-800 border-sky-200",
    delivering: "bg-sky-50 text-sky-800 border-sky-200",
    completed: "bg-emerald-50 text-emerald-700 border-emerald-200",
    cancelled: "bg-red-50 text-red-700 border-red-200",
    refunded: "bg-slate-100 text-slate-700 border-slate-300",
}

const PAYMENT_BADGE = {
    pending: "bg-amber-50 text-amber-800 border-amber-200",
    paid: "bg-emerald-50 text-emerald-700 border-emerald-200",
    failed: "bg-red-50 text-red-700 border-red-200",
}

const CANCELLABLE_STATUSES = ["pending", "new", "processing"]

const OrderHistory = ({ orders = [], onViewOrder, onOrderCancelled }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency, formatDate, getLocalizedText } = useFormatters()
    const [statusFilter, setStatusFilter] = useState("all")
    const [timeFilter, setTimeFilter] = useState("all")
    const [cancellingId, setCancellingId] = useState(null)

    const filteredOrders = useMemo(() => {
        return orders.filter((order) => {
            if (statusFilter !== "all" && order.status !== statusFilter) return false

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

    const handleCancelOrder = async (e, order) => {
        e.stopPropagation()
        if (!window.confirm(translate("order.cancel_confirm") || "Are you sure you want to cancel this order?")) {
            return
        }
        const reason = window.prompt(
            translate("order.cancel_reason_prompt") || "Please enter a reason for cancellation (optional):",
            ""
        )
        if (reason === null) return

        setCancellingId(order.id)
        try {
            await orderService.cancelOrder(order.id, reason)
            toast.success(translate("order.cancel_success") || "Order has been cancelled successfully.")
            onOrderCancelled?.()
        } catch (err) {
            toast.error(
                err?.response?.data?.message ||
                    translate("order.cancel_failed") ||
                    "Unable to cancel this order."
            )
        } finally {
            setCancellingId(null)
        }
    }

    return (
        <Card className="p-6 sm:p-8 tabular-nums">
            <div className="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pb-5 mb-6 border-b border-slate-100">
                <div>
                    <h2 className="text-lg font-bold text-[#09253E] tracking-tight">
                        {translate("order.history_title") || "Order History"}
                    </h2>
                    <p className="text-xs text-slate-500 mt-0.5">
                        {translate("order.history_subtitle") ||
                            "Inspect past purchases, invoice totals, and fulfillment status."}
                    </p>
                </div>

                <div className="flex items-center gap-2.5">
                    <select
                        value={statusFilter}
                        onChange={(e) => setStatusFilter(e.target.value)}
                        className="px-3 py-2 border border-slate-300 rounded-lg text-xs font-medium bg-white text-slate-800 focus:outline-none focus:border-[#004687]"
                    >
                        <option value="all">{translate("order.all_statuses") || "All Statuses"}</option>
                        <option value="pending">{translate("order.pending") || "Pending"}</option>
                        <option value="processing">{translate("order.processing") || "Processing"}</option>
                        <option value="shipping">{translate("order.shipping") || "Shipping"}</option>
                        <option value="completed">{translate("order.completed") || "Completed"}</option>
                        <option value="cancelled">{translate("order.cancelled") || "Cancelled"}</option>
                    </select>

                    <select
                        value={timeFilter}
                        onChange={(e) => setTimeFilter(e.target.value)}
                        className="px-3 py-2 border border-slate-300 rounded-lg text-xs font-medium bg-white text-slate-800 focus:outline-none focus:border-[#004687]"
                    >
                        <option value="all">{translate("order.all_time") || "All Time"}</option>
                        <option value="30_days">{translate("order.last_30_days") || "Last 30 Days"}</option>
                        <option value="6_months">{translate("order.last_6_months") || "Last 6 Months"}</option>
                        <option value="1_year">{translate("order.past_year") || "Past Year"}</option>
                    </select>
                </div>
            </div>

            <div className="space-y-3.5">
                {filteredOrders.map((order) => {
                    const firstItem = order.items && order.items.length > 0 ? order.items[0] : null
                    const extraItemsCount = order.items ? order.items.length - 1 : 0
                    const canCancel = CANCELLABLE_STATUSES.includes(String(order.status || "").toLowerCase())

                    return (
                        <div
                            key={order.id}
                            onClick={() => onViewOrder(order)}
                            className="group p-4 sm:p-5 border border-slate-200/90 rounded-xl hover:border-[#004687]/60 hover:bg-slate-50/50 transition-all cursor-pointer flex flex-col sm:flex-row justify-between sm:items-center gap-4"
                        >
                            <div className="flex items-start sm:items-center gap-4 flex-1 min-w-0">
                                <div className="w-14 h-14 bg-slate-100 rounded-lg border border-slate-200/70 overflow-hidden flex-shrink-0 flex items-center justify-center text-slate-400">
                                    {firstItem?.product?.image?.url ? (
                                        <img
                                            src={firstItem.product.image.url}
                                            alt={getLocalizedText(firstItem.product.name)}
                                            className="w-full h-full object-cover"
                                            onError={(e) => {
                                                e.currentTarget.style.display = "none"
                                                if (e.currentTarget.nextSibling) {
                                                    e.currentTarget.nextSibling.style.display = "flex"
                                                }
                                            }}
                                        />
                                    ) : null}
                                    <div
                                        className={`w-full h-full items-center justify-center ${
                                            firstItem?.product?.image?.url ? "hidden" : "flex"
                                        }`}
                                    >
                                        <Package size={22} />
                                    </div>
                                </div>
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2 flex-wrap mb-1">
                                        <span className="font-mono font-bold text-sm text-[#09253E]">
                                            #{order.number || order.id}
                                        </span>
                                        <span
                                            className={`text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded border ${
                                                STATUS_BADGE[order.status] ||
                                                "bg-slate-100 text-slate-600 border-slate-200"
                                            }`}
                                        >
                                            {translate(`order.${order.status}`) || order.status}
                                        </span>
                                        {order.payment_status && (
                                            <span
                                                className={`text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded border ${
                                                    PAYMENT_BADGE[order.payment_status] ||
                                                    "bg-slate-100 text-slate-600 border-slate-200"
                                                }`}
                                            >
                                                {translate(`payments.${order.payment_status}`) || order.payment_status}
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-xs text-slate-500 mb-1">
                                        {translate("order.placed_on") || "Placed on"} {formatDate(order.created_at)}
                                    </p>
                                    {firstItem && (
                                        <p className="text-xs font-semibold text-slate-800 truncate">
                                            {getLocalizedText(firstItem.product?.name || firstItem.name) || "Item"} (×{firstItem.qty})
                                            {extraItemsCount > 0 && (
                                                <span className="text-slate-500 font-normal ml-1">
                                                    + {extraItemsCount}
                                                </span>
                                            )}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="flex sm:flex-col justify-between items-center sm:items-end pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 shrink-0 gap-2">
                                <div className="text-left sm:text-right">
                                    <p className="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">
                                        {translate("order.total") || "Total"}
                                    </p>
                                    <p className="font-extrabold text-[#004687] text-base">
                                        {formatCurrency(order.total)}
                                    </p>
                                </div>
                                <div className="flex items-center gap-3">
                                    {canCancel && (
                                        <button
                                            type="button"
                                            disabled={cancellingId === order.id}
                                            onClick={(e) => handleCancelOrder(e, order)}
                                            className="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700"
                                        >
                                            <XCircle size={13} />
                                            <span>
                                                {cancellingId === order.id
                                                    ? translate("order.cancelling") || "Cancelling..."
                                                    : translate("order.cancel_order") || "Cancel"}
                                            </span>
                                        </button>
                                    )}
                                    <span className="inline-flex items-center gap-1 text-xs font-semibold text-[#004687] group-hover:translate-x-0.5 transition-transform">
                                        <span>{translate("order.details") || "Details"}</span>
                                        <ChevronRight size={14} />
                                    </span>
                                </div>
                            </div>
                        </div>
                    )
                })}
            </div>

            {filteredOrders.length === 0 && (
                <div className="py-16 flex flex-col items-center justify-center text-center">
                    <div className="w-12 h-12 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center mb-3">
                        <Package size={24} />
                    </div>
                    <p className="text-[#09253E] font-bold text-sm mb-1">
                        {translate("order.no_matching_orders") || "No matching orders found"}
                    </p>
                    <p className="text-slate-500 text-xs">
                        {translate("order.no_matching_orders_desc") ||
                            "Adjust your filter selection or browse the catalog to place an order."}
                    </p>
                </div>
            )}
        </Card>
    )
}

export default OrderHistory
