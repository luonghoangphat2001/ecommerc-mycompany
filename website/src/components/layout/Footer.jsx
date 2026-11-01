import React from "react"
import { Link } from "react-router-dom"
import { Mail, Phone, MapPin, ShieldCheck, Truck, CreditCard } from "lucide-react"
import useSettingsStore from "../../store/useSettingsStore"
import { useMenu } from "../../features/menu/hooks/useMenu"
import { getLocalizedText } from "../../utils/formatters"

const Footer = () => {
    const { settings, translate } = useSettingsStore()
    const { menuItems: footerMenuItems } = useMenu("footer-menu")

    const { general, footer } = settings || {}
    const currentYear = new Date().getFullYear()

    const storeName = general?.site_name || general?.store_name || "NovaStore"
    const storeEmail = general?.store_email || "support@novastore.com"
    const storePhone = general?.store_phone || "1900 1234"
    const storeCountry = general?.store_country || "Vietnam"
    const copyright = getLocalizedText(footer?.copyright) || `© ${currentYear} ${storeName}. All rights reserved.`

    return (
        <footer className="w-full bg-[#09253E] text-slate-300 border-t border-slate-800 mt-12">
            {/* Main Footer Grid */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-14">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-10">
                    {/* Brand & Contact Column */}
                    <div className="lg:col-span-4 space-y-4">
                        <div className="flex items-center gap-2.5">
                            <div className="w-8 h-8 rounded-lg bg-[#004687] border border-white/15 flex items-center justify-center font-extrabold text-base text-[#FBAE17]">
                                {storeName.charAt(0) || "N"}
                            </div>
                            <span className="text-lg font-extrabold text-white tracking-tight">{storeName}</span>
                        </div>

                        <p className="text-sm text-slate-400 leading-relaxed max-w-sm">
                            {translate?.("footer.description") ||
                                "Enterprise-grade retail & distribution platform offering verified products, transparent inventory, and nationwide fulfillment."}
                        </p>

                        <div className="pt-1 space-y-2 text-sm tabular-nums">
                            {storeEmail && (
                                <a
                                    href={`mailto:${storeEmail}`}
                                    className="flex items-center gap-2.5 text-slate-300 hover:text-white transition-colors"
                                >
                                    <Mail size={15} className="text-[#FBAE17] flex-shrink-0" />
                                    <span>{storeEmail}</span>
                                </a>
                            )}
                            {storePhone && (
                                <a
                                    href={`tel:${storePhone}`}
                                    className="flex items-center gap-2.5 text-slate-300 hover:text-white transition-colors"
                                >
                                    <Phone size={15} className="text-[#FBAE17] flex-shrink-0" />
                                    <span>{storePhone}</span>
                                </a>
                            )}
                            {storeCountry && (
                                <div className="flex items-center gap-2.5 text-slate-400">
                                    <MapPin size={15} className="text-[#FBAE17] flex-shrink-0" />
                                    <span>{storeCountry}</span>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Navigation Links */}
                    <div className="lg:col-span-2 space-y-3">
                        <h4 className="text-xs font-bold uppercase tracking-wider text-white">
                            {translate?.("footer.quick_links") || "Navigation"}
                        </h4>
                        <ul className="space-y-2 text-sm">
                            <li>
                                <Link to="/" className="text-slate-400 hover:text-white transition-colors">
                                    {translate?.("header.home") || "Home"}
                                </Link>
                            </li>
                            <li>
                                <Link to="/shop" className="text-slate-400 hover:text-white transition-colors">
                                    {translate?.("header.shop") || "Product Catalog"}
                                </Link>
                            </li>
                            <li>
                                <Link to="/posts" className="text-slate-400 hover:text-white transition-colors">
                                    {translate?.("header.blog") || "News & Insights"}
                                </Link>
                            </li>
                            <li>
                                <Link to="/my-account" className="text-slate-400 hover:text-white transition-colors">
                                    {translate?.("account.title") || "Customer Portal"}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Customer Policies */}
                    <div className="lg:col-span-3 space-y-3">
                        <h4 className="text-xs font-bold uppercase tracking-wider text-white">
                            {translate?.("footer.customer_service") || "Customer Support"}
                        </h4>
                        <ul className="space-y-2 text-sm">
                            {footerMenuItems?.length > 0 ? (
                                footerMenuItems.map((item) => (
                                    <li key={item.id}>
                                        <Link
                                            to={item.url || `/${item.slug}`}
                                            className="text-slate-400 hover:text-white transition-colors"
                                        >
                                            {getLocalizedText(item.label || item.title)}
                                        </Link>
                                    </li>
                                ))
                            ) : (
                                <>
                                    <li>
                                        <Link to="/shipping" className="text-slate-400 hover:text-white transition-colors">
                                            {translate?.("footer.shipping") || "Shipping & Delivery SLA"}
                                        </Link>
                                    </li>
                                    <li>
                                        <Link to="/returns" className="text-slate-400 hover:text-white transition-colors">
                                            {translate?.("footer.returns") || "Returns & Refund Policy"}
                                        </Link>
                                    </li>
                                    <li>
                                        <Link to="/privacy" className="text-slate-400 hover:text-white transition-colors">
                                            {translate?.("footer.privacy") || "Privacy & Data Protection"}
                                        </Link>
                                    </li>
                                    <li>
                                        <Link to="/terms" className="text-slate-400 hover:text-white transition-colors">
                                            {translate?.("footer.terms") || "Terms of Service"}
                                        </Link>
                                    </li>
                                </>
                            )}
                        </ul>
                    </div>

                    {/* Service Standards & Payment Methods */}
                    <div className="lg:col-span-3 space-y-4">
                        <h4 className="text-xs font-bold uppercase tracking-wider text-white">
                            {translate?.("footer.payment_methods") || "Fulfillment & Settlement"}
                        </h4>

                        <div className="space-y-2.5 text-xs text-slate-400">
                            <div className="flex items-center gap-2.5">
                                <ShieldCheck size={15} className="text-[#FBAE17] flex-shrink-0" />
                                <span>{translate?.("footer.quality_inspected") || "100% Quality-Inspected Inventory"}</span>
                            </div>
                            <div className="flex items-center gap-2.5">
                                <Truck size={15} className="text-[#FBAE17] flex-shrink-0" />
                                <span>{translate?.("footer.multi_warehouse") || "Real-Time Multi-Warehouse Dispatch"}</span>
                            </div>
                            <div className="flex items-center gap-2.5">
                                <CreditCard size={15} className="text-[#FBAE17] flex-shrink-0" />
                                <span>{translate?.("footer.encrypted_payment") || "Encrypted Payment Processing"}</span>
                            </div>
                        </div>

                        <div className="pt-2">
                            <div className="flex gap-1.5 flex-wrap">
                                {["COD", "Bank Transfer", "VNPay", "MoMo", "Visa / MC"].map((method) => (
                                    <span
                                        key={method}
                                        className="px-2.5 py-1 bg-white/5 border border-white/10 rounded-md text-[11px] font-semibold text-slate-300"
                                    >
                                        {method}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Bottom Legal Bar */}
            <div className="border-t border-white/10 bg-[#061A2C]">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div className="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                        <p>{copyright}</p>
                        <div className="flex items-center gap-3 tabular-nums">
                            <span>{translate?.("footer.currency") || "Currency"}: {general?.default_currency || "VND"}</span>
                            <span className="text-slate-600">•</span>
                            <span>{translate?.("footer.iso_standard") || "ISO-Standard E-Commerce Platform"}</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    )
}

export default Footer
