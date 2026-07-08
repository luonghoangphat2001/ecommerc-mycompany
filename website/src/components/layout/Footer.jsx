import React from "react"
import { Link } from "react-router-dom"
import { Mail, Phone, MapPin } from "lucide-react"
import useSettingsStore from "../../store/useSettingsStore"
import { useMenu } from "../../features/menu/hooks/useMenu"
import { useFormatters } from "../../utils/useFormatters"

const Footer = () => {
    const { settings, translate } = useSettingsStore()
    const { menuItems: footerMenuItems } = useMenu('footer-menu')
    const { formatCurrency } = useFormatters()

    const { general, footer } = settings || {}
    const currentYear = new Date().getFullYear()

    // Store info from settings
    const storeName = general?.site_name || general?.store_name || "My E-commerce"
    const storeEmail = general?.store_email || "contact@example.com"
    const storePhone = general?.store_phone || "1900 1234"
    const storeCountry = general?.store_country || "Vietnam"
    const copyright = footer?.copyright || `© ${currentYear} ${storeName}. All rights reserved.`
    const footerLinks = footer?.links || []

    return (
        <footer className="w-full bg-slate-900 text-slate-300 z-10">
            {/* Main Footer */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    {/* Store Info */}
                    <div className="space-y-4">
                        <h3 className="text-white font-bold text-lg">{storeName}</h3>
                        <p className="text-sm text-slate-400 leading-relaxed">
                            {translate?.("footer.description") || "Hệ thống e-commerce hàng đầu với đa dạng sản phẩm chất lượng, giá cả hợp lý và dịch vụ giao hàng nhanh chóng."}
                        </p>
                        <div className="space-y-2">
                            {storeEmail && (
                                <a href={`mailto:${storeEmail}`} className="flex items-center gap-2 text-sm hover:text-white transition-colors">
                                    <Mail size={16} />
                                    <span>{storeEmail}</span>
                                </a>
                            )}
                            {storePhone && (
                                <a href={`tel:${storePhone}`} className="flex items-center gap-2 text-sm hover:text-white transition-colors">
                                    <Phone size={16} />
                                    <span>{storePhone}</span>
                                </a>
                            )}
                            {storeCountry && (
                                <div className="flex items-center gap-2 text-sm text-slate-400">
                                    <MapPin size={16} />
                                    <span>{storeCountry}</span>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div className="space-y-4">
                        <h4 className="text-white font-semibold">{translate?.("footer.quick_links") || "Liên kết nhanh"}</h4>
                        <ul className="space-y-2">
                            <li>
                                <Link to="/" className="text-sm hover:text-white transition-colors">{translate?.("header.home") || "Trang chủ"}</Link>
                            </li>
                            <li>
                                <Link to="/shop" className="text-sm hover:text-white transition-colors">{translate?.("header.shop") || "Cửa hàng"}</Link>
                            </li>
                            <li>
                                <Link to="/posts" className="text-sm hover:text-white transition-colors">{translate?.("header.blog") || "Blog"}</Link>
                            </li>
                            <li>
                                <Link to="/about" className="text-sm hover:text-white transition-colors">{translate?.("header.about") || "Về chúng tôi"}</Link>
                            </li>
                            <li>
                                <Link to="/contact" className="text-sm hover:text-white transition-colors">{translate?.("header.contact") || "Liên hệ"}</Link>
                            </li>
                        </ul>
                    </div>

                    {/* Footer Menu from API */}
                    <div className="space-y-4">
                        <h4 className="text-white font-semibold">{translate?.("footer.customer_service") || "Hỗ trợ khách hàng"}</h4>
                        <ul className="space-y-2">
                            {footerMenuItems?.length > 0 ? (
                                footerMenuItems.map((item) => (
                                    <li key={item.id}>
                                        <Link 
                                            to={item.url || `/${item.slug}`} 
                                            className="text-sm hover:text-white transition-colors"
                                        >
                                            {item.label}
                                        </Link>
                                    </li>
                                ))
                            ) : (
                                <>
                                    <li><Link to="/shipping" className="text-sm hover:text-white transition-colors">{translate?.("footer.shipping") || "Chính sách vận chuyển"}</Link></li>
                                    <li><Link to="/returns" className="text-sm hover:text-white transition-colors">{translate?.("footer.returns") || "Đổi trả & Hoàn tiền"}</Link></li>
                                    <li><Link to="/faq" className="text-sm hover:text-white transition-colors">{translate?.("footer.faq") || "Câu hỏi thường gặp"}</Link></li>
                                    <li><Link to="/privacy" className="text-sm hover:text-white transition-colors">{translate?.("footer.privacy") || "Chính sách bảo mật"}</Link></li>
                                    <li><Link to="/terms" className="text-sm hover:text-white transition-colors">{translate?.("footer.terms") || "Điều khoản sử dụng"}</Link></li>
                                </>
                            )}
                        </ul>
                    </div>

                    {/* Social & Newsletter */}
                    <div className="space-y-4">
                        <h4 className="text-white font-semibold">{translate?.("footer.follow_us") || "Theo dõi chúng tôi"}</h4>
                        <div className="flex gap-3">
                            <a href="#" className="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-blue-600 transition-colors text-white text-sm font-bold">f</a>
                            <a href="#" className="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-pink-600 transition-colors text-white text-sm font-bold">IG</a>
                            <a href="#" className="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-sky-500 transition-colors text-white text-sm font-bold">X</a>
                            <a href="#" className="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-red-600 transition-colors text-white text-sm font-bold">YT</a>
                        </div>
                        
                        {/* Payment Methods */}
                        <div className="pt-4">
                            <h5 className="text-xs text-slate-400 mb-2">{translate?.("footer.payment_methods") || "Phương thức thanh toán"}</h5>
                            <div className="flex gap-2 flex-wrap">
                                <span className="px-2 py-1 bg-slate-800 rounded text-xs">COD</span>
                                <span className="px-2 py-1 bg-slate-800 rounded text-xs">Bank</span>
                                <span className="px-2 py-1 bg-slate-800 rounded text-xs">VNPay</span>
                                <span className="px-2 py-1 bg-slate-800 rounded text-xs">MoMo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Bottom Bar */}
            <div className="border-t border-slate-800">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                        <p className="text-sm text-slate-500">{copyright}</p>
                        <div className="flex items-center gap-4 text-sm text-slate-500">
                            <span>{translate?.("footer.language") || "Ngôn ngữ"}: Tiếng Việt</span>
                            <span>|</span>
                            <span>{translate?.("footer.currency") || "Tiền tệ"}: {general?.default_currency || "VND"}</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    )
}

export default Footer
