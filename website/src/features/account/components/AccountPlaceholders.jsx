import React, { useState, useEffect } from "react"
import { Package, CreditCard, Tag, Star, User, MapPin, ShieldCheck, ArrowRight } from "lucide-react"
import EmptyState from "../../../components/common/EmptyState"
import Card from "../../../components/common/Card"
import apiService from "../../../api/apiService"
import orderService from "../../order/services/orderService"
import { unwrapApiList, unwrapApiObject } from "../../../api/apiResponse"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"
import { formatOrderDate } from "../../../utils/date"

export const AccountDashboard = ({ user, ordersCount = 0, onNavigateTab }) => {
    const translate = useSettingsStore((state) => state.translate)

    return (
        <div className="space-y-6">
            <div className="bg-white border border-slate-200/90 rounded-xl p-6 shadow-card">
                <h2 className="text-xl font-bold text-[#09253E] tracking-tight mb-1">
                    {translate("account.dashboard_title") || "Account Overview"}
                </h2>
                <p className="text-sm text-slate-500">
                    {translate("account.dashboard_subtitle") ||
                        "Manage your personal profile, shipping addresses, and order history."}
                </p>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 tabular-nums">
                    <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                        <div className="flex items-center justify-between text-slate-500 mb-2">
                            <span className="text-xs font-semibold uppercase tracking-wider">
                                {translate("account.total_orders") || "Total Orders"}
                            </span>
                            <Package size={16} className="text-[#004687]" />
                        </div>
                        <div className="text-2xl font-extrabold text-[#09253E]">{ordersCount}</div>
                        {onNavigateTab && (
                            <button
                                type="button"
                                onClick={() => onNavigateTab("orders")}
                                className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-[#004687] hover:underline"
                            >
                                <span>{translate("account.view_orders") || "View orders"}</span>
                                <ArrowRight size={12} />
                            </button>
                        )}
                    </div>

                    <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                        <div className="flex items-center justify-between text-slate-500 mb-2">
                            <span className="text-xs font-semibold uppercase tracking-wider">
                                {translate("account.account_holder") || "Account Holder"}
                            </span>
                            <User size={16} className="text-[#004687]" />
                        </div>
                        <div>
                            <div className="text-sm font-bold text-[#09253E] truncate">{user?.name}</div>
                            <div className="text-xs text-slate-500 truncate">{user?.email}</div>
                        </div>
                        {onNavigateTab && (
                            <button
                                type="button"
                                onClick={() => onNavigateTab("profile")}
                                className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-[#004687] hover:underline"
                            >
                                <span>{translate("account.edit_profile") || "Edit profile"}</span>
                                <ArrowRight size={12} />
                            </button>
                        )}
                    </div>

                    <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                        <div className="flex items-center justify-between text-slate-500 mb-2">
                            <span className="text-xs font-semibold uppercase tracking-wider">
                                {translate("account.addresses_security") || "Addresses & Security"}
                            </span>
                            <MapPin size={16} className="text-[#004687]" />
                        </div>
                        <div className="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                            <ShieldCheck size={14} />
                            <span>{translate("account.active_protected") || "Active & Protected"}</span>
                        </div>
                        {onNavigateTab && (
                            <button
                                type="button"
                                onClick={() => onNavigateTab("address")}
                                className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-[#004687] hover:underline"
                            >
                                <span>{translate("account.manage_addresses") || "Manage addresses"}</span>
                                <ArrowRight size={12} />
                            </button>
                        )}
                    </div>
                </div>
            </div>
        </div>
    )
}

export const PaymentHistory = ({ orders: propOrders = [] }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()
    const [orders, setOrders] = useState(propOrders)

    useEffect(() => {
        if (propOrders && propOrders.length > 0) {
            setOrders(propOrders)
        } else {
            orderService
                .getAll({ include: "payments" })
                .then((res) => setOrders(unwrapApiList(res, [])))
                .catch(() => {})
        }
    }, [propOrders])

    const transactions = orders.flatMap((order) => {
        if (Array.isArray(order.payments) && order.payments.length > 0) {
            return order.payments.map((p) => ({
                id: p.id || `${order.id}-${p.created_at}`,
                orderNumber: order.number || order.id,
                method: p.payment_method || order.payment_method || "cod",
                status: p.status || order.payment_status || "pending",
                amount: p.amount ?? order.total,
                date: p.created_at || order.created_at,
            }))
        }
        return [
            {
                id: order.id,
                orderNumber: order.number || order.id,
                method: order.payment_method || "cod",
                status: order.payment_status || "pending",
                amount: order.total,
                date: order.created_at,
            },
        ]
    })

    return (
        <Card className="p-6 tabular-nums">
            <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
                {translate("account.payments_title") || "Payment History"}
            </h2>
            {transactions.length === 0 ? (
                <EmptyState
                    icon={CreditCard}
                    title={translate("account.payments_empty_title") || "No payment transactions recorded"}
                    description={
                        translate("account.payments_empty_desc") ||
                        "Completed payment settlements for your orders will appear here."
                    }
                />
            ) : (
                <div className="divide-y divide-slate-100">
                    {transactions.map((tx) => (
                        <div key={tx.id} className="py-3.5 flex items-center justify-between gap-4 text-xs">
                            <div>
                                <p className="font-bold text-slate-900 text-sm">#{tx.orderNumber}</p>
                                <p className="text-slate-500 mt-0.5">
                                    {formatOrderDate(tx.date)} •{" "}
                                    <span className="uppercase font-semibold">{tx.method}</span>
                                </p>
                            </div>
                            <div className="text-right">
                                <p className="font-bold text-[#004687] text-sm">{formatCurrency(tx.amount)}</p>
                                <span className="inline-block mt-0.5 px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold uppercase text-[10px]">
                                    {translate(`payments.${tx.status}`) || tx.status}
                                </span>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </Card>
    )
}

export const RefundHistory = ({ orders: propOrders = [] }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()
    const [orders, setOrders] = useState(propOrders)

    useEffect(() => {
        if (propOrders && propOrders.length > 0) {
            setOrders(propOrders)
        } else {
            orderService
                .getAll({ include: "refunds" })
                .then((res) => setOrders(unwrapApiList(res, [])))
                .catch(() => {})
        }
    }, [propOrders])

    const refunds = orders.flatMap((order) => {
        if (Array.isArray(order.refunds) && order.refunds.length > 0) {
            return order.refunds.map((r, idx) => ({
                id: r.id || `${order.id}-${idx}`,
                orderNumber: order.number || order.id,
                amount: r.amount ?? order.total,
                status: r.status || "completed",
                reason: r.reason,
                date: r.created_at || order.updated_at || order.created_at,
            }))
        }
        if (order.status === "refunded") {
            return [
                {
                    id: order.id,
                    orderNumber: order.number || order.id,
                    amount: order.total,
                    status: "refunded",
                    reason: "",
                    date: order.updated_at || order.created_at,
                },
            ]
        }
        return []
    })

    return (
        <Card className="p-6 tabular-nums">
            <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
                {translate("account.refunds_title") || "Returns & Refunds"}
            </h2>
            {refunds.length === 0 ? (
                <EmptyState
                    icon={Package}
                    title={translate("account.refunds_empty_title") || "No active refund requests"}
                    description={
                        translate("account.refunds_empty_desc") ||
                        "You do not have any pending or completed return requests."
                    }
                />
            ) : (
                <div className="divide-y divide-slate-100">
                    {refunds.map((refund) => (
                        <div key={refund.id} className="py-3.5 flex items-center justify-between gap-4 text-xs">
                            <div>
                                <p className="font-bold text-slate-900 text-sm">#{refund.orderNumber}</p>
                                <p className="text-slate-500 mt-0.5">{formatOrderDate(refund.date)}</p>
                                {refund.reason && <p className="text-slate-600 mt-1">{refund.reason}</p>}
                            </div>
                            <div className="text-right">
                                <p className="font-bold text-red-600 text-sm">{formatCurrency(refund.amount)}</p>
                                <span className="inline-block mt-0.5 px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold uppercase text-[10px]">
                                    {translate(`order.${refund.status}`) || refund.status}
                                </span>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </Card>
    )
}

export const CouponHistory = ({ orders: propOrders = [] }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()
    const [usages, setUsages] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        let isMounted = true
        const loadCoupons = async () => {
            setLoading(true)
            try {
                const res = await apiService.get("coupons/my-usages")
                const list = unwrapApiList(res, [])
                if (isMounted && list.length > 0) {
                    setUsages(list)
                    setLoading(false)
                    return
                }
            } catch (e) {
                // fallback to coupons found in user orders
            }

            if (isMounted) {
                const fromOrders = (propOrders || []).flatMap((order) =>
                    (order.coupons || []).map((c) => ({
                        id: `${order.id}-${c.code}`,
                        code: c.code,
                        discount_amount: c.discount_amount || order.discount_total,
                        order_number: order.number || order.id,
                        used_at: order.created_at,
                    }))
                )
                setUsages(fromOrders)
                setLoading(false)
            }
        }
        loadCoupons()
        return () => {
            isMounted = false
        }
    }, [propOrders])

    return (
        <Card className="p-6 tabular-nums">
            <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
                {translate("account.coupons_title") || "My Vouchers & Promotions"}
            </h2>
            {loading ? (
                <p className="text-xs text-slate-500 py-8 text-center">{translate("common.loading")}</p>
            ) : usages.length === 0 ? (
                <EmptyState
                    icon={Tag}
                    title={translate("account.coupons_empty_title") || "No active vouchers"}
                    description={
                        translate("account.coupons_empty_desc") ||
                        "Promotional codes assigned to your account will be listed here."
                    }
                />
            ) : (
                <div className="divide-y divide-slate-100">
                    {usages.map((usage, idx) => (
                        <div key={usage.id || idx} className="py-3.5 flex items-center justify-between gap-4 text-xs">
                            <div>
                                <span className="font-mono font-bold text-sm text-[#004687] bg-[#004687]/10 px-2.5 py-1 rounded">
                                    {usage.coupon?.code || usage.code}
                                </span>
                                {usage.order_number && (
                                    <span className="ml-2 text-slate-500">#{usage.order_number}</span>
                                )}
                                {usage.used_at && (
                                    <p className="text-slate-400 mt-1">{formatOrderDate(usage.used_at)}</p>
                                )}
                            </div>
                            {usage.discount_amount > 0 && (
                                <span className="font-bold text-emerald-700 text-sm">
                                    -{formatCurrency(usage.discount_amount)}
                                </span>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </Card>
    )
}

export const LoyaltyPoints = () => {
    const translate = useSettingsStore((state) => state.translate)
    const [pointsData, setPointsData] = useState(null)
    const [history, setHistory] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        let isMounted = true
        const fetchLoyalty = async () => {
            setLoading(true)
            try {
                const [pointsRes, historyRes] = await Promise.allSettled([
                    apiService.get("loyalty/points"),
                    apiService.get("loyalty/history"),
                ])

                if (isMounted) {
                    if (pointsRes.status === "fulfilled") {
                        setPointsData(unwrapApiObject(pointsRes.value))
                    }
                    if (historyRes.status === "fulfilled") {
                        setHistory(unwrapApiList(historyRes.value, []))
                    }
                }
            } catch (e) {
                // ignore
            } finally {
                if (isMounted) setLoading(false)
            }
        }
        fetchLoyalty()
        return () => {
            isMounted = false
        }
    }, [])

    const currentPoints = pointsData?.points ?? pointsData?.balance ?? 0
    const lifetimePoints = pointsData?.lifetime_points ?? pointsData?.total_earned ?? currentPoints

    return (
        <Card className="p-6 tabular-nums">
            <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
                {translate("account.loyalty_title") || "Loyalty Rewards Ledger"}
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div className="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wider text-amber-800">
                            {translate("account.loyalty_points_balance") || "Reward Points Balance"}
                        </p>
                        <p className="text-2xl font-extrabold text-[#09253E] mt-1">{currentPoints}</p>
                    </div>
                    <Star size={24} className="text-[#FBAE17]" />
                </div>

                <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {translate("account.loyalty_lifetime_points") || "Lifetime Points Earned"}
                        </p>
                        <p className="text-2xl font-extrabold text-[#004687] mt-1">{lifetimePoints}</p>
                    </div>
                    <ShieldCheck size={24} className="text-[#004687]" />
                </div>
            </div>

            {loading ? (
                <p className="text-xs text-slate-500 py-6 text-center">{translate("common.loading")}</p>
            ) : history.length === 0 ? (
                <EmptyState
                    icon={Star}
                    title={`${currentPoints} ${translate("account.loyalty_title") || "Reward Points"}`}
                    description={
                        translate("account.loyalty_empty_desc") ||
                        "Earn reward points automatically on every completed purchase."
                    }
                />
            ) : (
                <div>
                    <h3 className="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">
                        {translate("account.loyalty_history") || "Points Transaction History"}
                    </h3>
                    <div className="divide-y divide-slate-100">
                        {history.map((item, idx) => (
                            <div key={item.id || idx} className="py-3 flex items-center justify-between text-xs">
                                <div>
                                    <p className="font-semibold text-slate-800">
                                        {item.description || item.type || "Points Transaction"}
                                    </p>
                                    <p className="text-slate-400 mt-0.5">{formatOrderDate(item.created_at)}</p>
                                </div>
                                <span
                                    className={`font-bold text-sm ${
                                        item.points >= 0 ? "text-emerald-700" : "text-red-600"
                                    }`}
                                >
                                    {item.points > 0 ? `+${item.points}` : item.points}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </Card>
    )
}
