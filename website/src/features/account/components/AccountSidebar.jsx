import React from "react"
import { User, Package, LogOut, ChevronRight, Lock, Truck, MapPin } from "lucide-react"
import useSettingsStore from "../../../store/useSettingsStore"

const AccountSidebar = ({ user, onLogout, activeTab, setActiveTab }) => {
    const translate = useSettingsStore((state) => state.translate)
    const menuItems = [
        { id: "orders", label: translate("account.orders_title"), icon: Package },
        { id: "profile", label: translate("account.profile_title"), icon: User },
        { id: "address", label: translate("account.addresses_title"), icon: MapPin },
        { id: "password", label: translate("account.password_title"), icon: Lock },
        { id: "tracking", label: translate("account.tracking_title"), icon: Truck },
    ]

    return (
        <aside className="w-full md:w-80 space-y-4">
            <div className="bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-8 shadow-[0_8px_30px_rgba(0,0,0,0.02)]">
                <div className="flex flex-col items-center text-center mb-8">
                    <div className="w-20 h-20 bg-gradient-to-tr from-blue-100 to-purple-100 rounded-full flex items-center justify-center text-blue-600 mb-4 shadow-inner">
                        <User size={32} />
                    </div>
                    <h2 className="text-xl font-bold text-slate-900">{user?.name}</h2>
                    <p className="text-sm text-slate-500">{user?.email}</p>
                </div>

                <nav className="space-y-2">
                    {menuItems.map((item) => (
                        <button key={item.id} onClick={() => setActiveTab(item.id)} className={`w-full flex items-center justify-between p-4 rounded-2xl transition-all ${activeTab === item.id ? "bg-blue-600 text-white shadow-lg shadow-blue-100" : "text-slate-600 hover:bg-slate-50 font-semibold"}`}>
                            <div className="flex items-center gap-3">
                                <item.icon size={20} />
                                <span className={activeTab === item.id ? "font-bold" : ""}>{item.label}</span>
                            </div>
                            <ChevronRight size={18} opacity={activeTab === item.id ? 1 : 0.5} />
                        </button>
                    ))}

                    <button onClick={onLogout} className="w-full flex items-center justify-between p-4 text-red-500 hover:bg-red-50 rounded-2xl font-semibold transition-all mt-4">
                        <div className="flex items-center gap-3">
                            <LogOut size={20} />
                            <span>{translate("header.logout") || "Đăng xuất"}</span>
                        </div>
                    </button>
                </nav>
            </div>
        </aside>
    )
}

export default AccountSidebar
