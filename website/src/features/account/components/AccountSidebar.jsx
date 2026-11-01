import React from "react"
import {
    User,
    Package,
    LogOut,
    ChevronRight,
    Lock,
    MapPin,
    LayoutDashboard,
    CreditCard,
    RotateCcw,
    Ticket,
    Award,
    Truck,
} from "lucide-react"
import useSettingsStore from "../../../store/useSettingsStore"

const AccountSidebar = ({ user, onLogout, activeTab, setActiveTab }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { getSetting } = useSettingsStore()
    const couponsEnabled = getSetting("marketing.enable_coupons") !== false
    const loyaltyEnabled = getSetting("marketing.enable_loyalty") !== false

    const menuItems = [
        { id: "dashboard", label: translate("account.dashboard_title") || "Overview", icon: LayoutDashboard },
        { id: "orders", label: translate("account.orders_title") || "Order History", icon: Package },
        { id: "tracking", label: translate("account.tracking_title") || "Order Tracking", icon: Truck },
        { id: "profile", label: translate("account.profile_title") || "Profile Details", icon: User },
        { id: "address", label: translate("account.addresses_title") || "Address Book", icon: MapPin },
        { id: "payments", label: translate("account.payments_title") || "Payment History", icon: CreditCard },
        { id: "refunds", label: translate("account.refunds_title") || "Returns & Refunds", icon: RotateCcw },
        ...(couponsEnabled
            ? [{ id: "coupons", label: translate("account.coupons_title") || "Vouchers", icon: Ticket }]
            : []),
        ...(loyaltyEnabled
            ? [{ id: "loyalty", label: translate("account.loyalty_title") || "Loyalty Points", icon: Award }]
            : []),
        { id: "password", label: translate("account.password_title") || "Security & Password", icon: Lock },
    ]

    return (
        <aside className="w-full lg:w-72 flex-shrink-0">
            <div className="bg-white border border-slate-200/90 rounded-xl p-5 shadow-card">
                <div className="flex items-center gap-3.5 pb-4 mb-4 border-b border-slate-100">
                    <div className="w-11 h-11 bg-[#004687]/10 rounded-lg flex items-center justify-center text-[#004687] font-bold text-base shrink-0">
                        {user?.name ? user.name.charAt(0).toUpperCase() : <User size={20} />}
                    </div>
                    <div className="min-w-0">
                        <h2 className="text-sm font-bold text-[#09253E] truncate">{user?.name}</h2>
                        <p className="text-xs text-slate-500 truncate">{user?.email}</p>
                    </div>
                </div>

                <nav className="space-y-1">
                    {menuItems.map((item) => {
                        const isActive = activeTab === item.id
                        return (
                            <button
                                key={item.id}
                                type="button"
                                onClick={() => setActiveTab(item.id)}
                                className={`w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors ${
                                    isActive
                                        ? "bg-[#004687] text-white shadow-card"
                                        : "text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                                }`}
                            >
                                <div className="flex items-center gap-2.5">
                                    <item.icon size={16} />
                                    <span>{item.label}</span>
                                </div>
                                <ChevronRight size={14} className={isActive ? "opacity-100" : "opacity-40"} />
                            </button>
                        )
                    })}

                    <div className="pt-2 mt-2 border-t border-slate-100">
                        <button
                            type="button"
                            onClick={onLogout}
                            className="w-full flex items-center gap-2.5 px-3 py-2.5 text-red-600 hover:bg-red-50 rounded-lg text-xs font-semibold transition-colors"
                        >
                            <LogOut size={16} />
                            <span>{translate("header.logout") || "Sign Out"}</span>
                        </button>
                    </div>
                </nav>
            </div>
        </aside>
    )
}

export default AccountSidebar
