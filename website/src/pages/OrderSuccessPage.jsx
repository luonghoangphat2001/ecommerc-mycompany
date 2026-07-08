import React, { useEffect } from "react"
import { useLocation, Link, useNavigate } from "react-router-dom"
import { CheckCircle, Package, Home, ShoppingBag } from "lucide-react"
import useSettingsStore from "../store/useSettingsStore"

const OrderSuccessPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const storeName = useSettingsStore((state) => state.getSetting("general.site_name") || state.getSetting("general.store_name") || "NovaStore")
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
        <div className="min-h-screen bg-slate-50 pt-32 pb-20 font-sans">
            <div className="max-w-3xl mx-auto px-4">
                <div className="bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.03)] border border-slate-100 overflow-hidden text-center p-12 md:p-20 relative">
                    {/* Background decoration */}
                    <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-600 to-purple-600"></div>
                    <div className="absolute -top-24 -right-24 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-60"></div>
                    <div className="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-50 rounded-full blur-3xl opacity-60"></div>

                    <div className="relative z-10">
                        <div className="w-24 h-24 bg-green-50 text-green-600 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce-slow">
                            <CheckCircle size={48} />
                        </div>

                        <h1 className="text-3xl md:text-4xl font-black text-slate-900 mb-4 tracking-tight">{translate("checkout.success_title")}</h1>
                        <p className="text-slate-500 text-lg mb-10 max-w-md mx-auto leading-relaxed">{translate("checkout.success_msg")}</p>

                        <div className="bg-slate-50 rounded-3xl p-6 md:p-8 mb-10 border border-slate-100 inline-block w-full max-w-md">
                            <div className="flex justify-between items-center mb-4 pb-4 border-b border-slate-200">
                                <span className="text-slate-500 font-medium">{translate("checkout.order_number")}:</span>
                                <span className="text-slate-900 font-black text-lg">{order.number}</span>
                            </div>
                            <div className="flex justify-between items-center">
                                <span className="text-slate-500 font-medium">{translate("checkout.order_status")}:</span>
                                <span className="bg-blue-50 text-blue-600 px-4 py-1 rounded-full text-sm font-bold">{order.status_label || translate("checkout.order_received")}</span>
                            </div>
                        </div>

                        <div className="flex flex-col md:flex-row items-center justify-center gap-4">
                            <Link to="/shop" className="w-full md:w-auto bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 px-8 rounded-2xl transition-all flex items-center justify-center gap-2">
                                <ShoppingBag size={20} />
                                {translate("checkout.continue_shopping")}
                            </Link>
                            <Link to="/my-account" className="w-full md:w-auto bg-white border border-slate-200 hover:border-blue-600 hover:text-blue-600 text-slate-700 font-bold py-4 px-8 rounded-2xl transition-all flex items-center justify-center gap-2">
                                <Package size={20} />
                                {translate("checkout.view_orders")}
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="mt-12 text-center">
                    <p className="text-slate-400 text-sm flex items-center justify-center gap-2">
                        <Home size={14} />
                        {storeName} &copy; {new Date().getFullYear()}
                    </p>
                </div>
            </div>
        </div>
    )
}

export default OrderSuccessPage
