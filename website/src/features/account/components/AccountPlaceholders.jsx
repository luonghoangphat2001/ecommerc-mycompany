import React from "react"
import { Package, CreditCard, Tag, Star, User, MapPin, ShieldCheck, ArrowRight } from "lucide-react"
import EmptyState from "../../../components/common/EmptyState"
import Card from "../../../components/common/Card"

export const AccountDashboard = ({ user, ordersCount = 0, onNavigateTab }) => (
    <div className="space-y-6">
        <div className="bg-white border border-slate-200/90 rounded-xl p-6 shadow-card">
            <h2 className="text-xl font-bold text-[#09253E] tracking-tight mb-1">Account Overview</h2>
            <p className="text-sm text-slate-500">
                Manage your personal profile, shipping addresses, and order history.
            </p>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 tabular-nums">
                <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div className="flex items-center justify-between text-slate-500 mb-2">
                        <span className="text-xs font-semibold uppercase tracking-wider">Total Orders</span>
                        <Package size={16} className="text-[#004687]" />
                    </div>
                    <div className="text-2xl font-extrabold text-[#09253E]">{ordersCount}</div>
                    {onNavigateTab && (
                        <button
                            type="button"
                            onClick={() => onNavigateTab("orders")}
                            className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-[#004687] hover:underline"
                        >
                            <span>View orders</span>
                            <ArrowRight size={12} />
                        </button>
                    )}
                </div>

                <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div className="flex items-center justify-between text-slate-500 mb-2">
                        <span className="text-xs font-semibold uppercase tracking-wider">Account Holder</span>
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
                            <span>Edit profile</span>
                            <ArrowRight size={12} />
                        </button>
                    )}
                </div>

                <div className="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div className="flex items-center justify-between text-slate-500 mb-2">
                        <span className="text-xs font-semibold uppercase tracking-wider">Addresses & Security</span>
                        <MapPin size={16} className="text-[#004687]" />
                    </div>
                    <div className="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                        <ShieldCheck size={14} />
                        <span>Active & Protected</span>
                    </div>
                    {onNavigateTab && (
                        <button
                            type="button"
                            onClick={() => onNavigateTab("address")}
                            className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-[#004687] hover:underline"
                        >
                            <span>Manage addresses</span>
                            <ArrowRight size={12} />
                        </button>
                    )}
                </div>
            </div>
        </div>
    </div>
)

export const PaymentHistory = () => (
    <Card className="p-6">
        <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
            Payment Transactions
        </h2>
        <EmptyState
            icon={CreditCard}
            title="No payment transactions recorded"
            description="Completed payment settlements for your orders will appear here."
        />
    </Card>
)

export const RefundHistory = () => (
    <Card className="p-6">
        <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
            Returns & Refund Requests
        </h2>
        <EmptyState
            icon={Package}
            title="No active refund requests"
            description="You do not have any pending or completed return requests."
        />
    </Card>
)

export const CouponHistory = () => (
    <Card className="p-6">
        <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
            My Vouchers & Promotions
        </h2>
        <EmptyState
            icon={Tag}
            title="No active vouchers"
            description="Promotional codes assigned to your account will be listed here."
        />
    </Card>
)

export const LoyaltyPoints = () => (
    <Card className="p-6">
        <h2 className="text-lg font-bold text-[#09253E] pb-4 mb-4 border-b border-slate-100">
            Loyalty Rewards Ledger
        </h2>
        <EmptyState
            icon={Star}
            title="0 Reward Points"
            description="Earn reward points automatically on every completed purchase."
        />
    </Card>
)
