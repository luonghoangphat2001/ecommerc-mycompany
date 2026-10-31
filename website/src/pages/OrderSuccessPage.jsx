import React, { useEffect } from "react"
import { useLocation, Link, useNavigate } from "react-router-dom"
import { CheckCircle, Package, ShoppingBag } from "lucide-react"
import useSettingsStore from "../store/useSettingsStore"
import Logo from "../components/common/Logo"

const OrderSuccessPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const storeName = useSettingsStore(
        (state) => state.getSetting("general.site_name") || state.getSetting("general.store_name") || "NovaStore"
    )
    const location = useLocation()
    const navigate = useNavigate()
    const order = location.state?.order

    useEffect(() => {
        if (!order) {
            navigate("/")
        }
        window.scrollTo(0, 0)
    }, [order, navigate])

    if (!order) return null

    return (
        <div className="min-h-screen bg-[#F8FAFC] py-16 px-4 font-sans flex flex-col items-center justify-center">
            <div className="mb-8">
                <Logo />
            </div>

            <div className="w-full max-w-xl bg-white rounded-xl shadow-card border border-slate-200/90 p-6 sm:p-10 text-center tabular-nums">
                <div className="w-14 h-14 bg-emerald-50 border border-emerald-200/70 text-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-5">
                    <CheckCircle size={28} strokeWidth={2.2} />
                </div>

                <h1 className="text-2xl sm:text-3xl font-bold text-[#09253E] mb-2 tracking-tight">
                    {translate("checkout.success_title")}
                </h1>
                <p className="text-slate-500 text-sm mb-7 max-w-md mx-auto leading-relaxed">
                    {translate("checkout.success_msg")}
                </p>

                <div className="bg-slate-50 rounded-xl p-4 sm:p-5 mb-7 border border-slate-200/80 text-left space-y-3">
                    <div className="flex justify-between items-center pb-3 border-b border-slate-200/80">
                        <span className="text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            {translate("checkout.order_number")}
                        </span>
                        <span className="text-[#09253E] font-bold text-sm font-mono">{order.number}</span>
                    </div>
                    <div className="flex justify-between items-center">
                        <span className="text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            {translate("checkout.order_status")}
                        </span>
                        <span className="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-md text-xs font-semibold">
                            {order.status_label || translate("checkout.order_received")}
                        </span>
                    </div>
                </div>

                <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link
                        to="/shop"
                        className="w-full sm:w-auto bg-[#004687] hover:bg-[#003669] text-white font-semibold py-2.5 px-5 rounded-lg transition-colors flex items-center justify-center gap-2 text-sm shadow-card"
                    >
                        <ShoppingBag size={16} />
                        <span>{translate("checkout.continue_shopping")}</span>
                    </Link>
                    <Link
                        to="/my-account?tab=orders"
                        className="w-full sm:w-auto bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-5 rounded-lg transition-colors flex items-center justify-center gap-2 text-sm"
                    >
                        <Package size={16} />
                        <span>{translate("checkout.view_orders")}</span>
                    </Link>
                </div>
            </div>

            <p className="mt-8 text-slate-400 text-xs">
                {storeName} &copy; {new Date().getFullYear()}
            </p>
        </div>
    )
}

export default OrderSuccessPage
