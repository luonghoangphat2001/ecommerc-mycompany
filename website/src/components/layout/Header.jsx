import React from "react"
import { Link } from "react-router-dom"
import { User, LogOut, Menu, X, Phone, ShieldCheck, PackageCheck } from "lucide-react"
import useHeader from "../../hooks/useHeader"

import Logo from "../common/Logo"
import SearchBar from "../../features/product/components/SearchBar"
import CartIcon from "../../features/cart/components/CartIcon"
import LanguageSwitcher from "./LanguageSwitcher"
import Navbar from "./Navbar"

const Header = () => {
    const {
        user,
        _hasHydrated,
        cartCount,
        settings,
        language,
        currency,
        isMobileMenuOpen,
        setLanguage,
        setCurrency,
        translate,
        handleLogout,
        toggleMobileMenu,
        closeMobileMenu,
        toggleCart,
    } = useHeader()

    const storePhone = settings?.general?.store_phone || "1900 1234"

    return (
        <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-card">
            {/* Top Corporate Utility Bar */}
            <div className="bg-[#09253E] text-slate-300 text-xs border-b border-white/10">
                <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
                    <div className="flex items-center gap-5">
                        <span className="inline-flex items-center gap-1.5 text-slate-200 font-medium">
                            <ShieldCheck size={13} className="text-[#FBAE17]" />
                            <span>{translate("home.hero.badge") || "Official Verified Distribution"}</span>
                        </span>
                        <span className="hidden sm:inline-flex items-center gap-1.5 text-slate-300 tabular-nums">
                            <Phone size={12} className="text-[#FBAE17]" />
                            <span>{storePhone}</span>
                        </span>
                    </div>

                    <div className="flex items-center gap-4">
                        <Link
                            to="/posts"
                            className="hidden md:inline-flex items-center gap-1 text-slate-300 hover:text-white transition-colors"
                        >
                            <span>{translate("header.blog") || "News & Insights"}</span>
                        </Link>
                        {user && (
                            <Link
                                to="/my-account?tab=orders"
                                className="hidden sm:inline-flex items-center gap-1 text-slate-300 hover:text-white transition-colors"
                            >
                                <PackageCheck size={13} className="text-[#FBAE17]" />
                                <span>{translate("account.tabs.orders") || "Orders"}</span>
                            </Link>
                        )}
                        <div className="hidden sm:block pl-3 border-l border-white/15">
                            <LanguageSwitcher
                                settings={settings}
                                currentLang={language}
                                onLangChange={setLanguage}
                                currentCurr={currency}
                                onCurrChange={setCurrency}
                                variant="dark"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Navigation Header */}
            <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <div className="flex items-center gap-8">
                    <Logo />
                    <Navbar translate={translate} />
                </div>

                <div className="flex-1 max-w-md hidden lg:block">
                    <SearchBar placeholder={translate("header.search_placeholder")} />
                </div>

                <div className="flex items-center gap-2 sm:gap-3">
                    <CartIcon count={cartCount} onClick={toggleCart} />

                    {!_hasHydrated ? (
                        <div className="w-8 h-8 ml-2 bg-slate-100 rounded-lg animate-pulse" />
                    ) : user ? (
                        <div className="hidden sm:flex items-center gap-2 pl-3 ml-1 border-l border-slate-200">
                            <Link
                                to="/my-account"
                                className="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-slate-100 transition-colors group"
                            >
                                <div className="w-7 h-7 bg-[#004687]/10 rounded-md flex items-center justify-center text-[#004687] font-bold text-xs">
                                    <User size={15} />
                                </div>
                                <span className="text-sm font-semibold text-slate-800 group-hover:text-[#004687] transition-colors max-w-[130px] truncate">
                                    {user.name}
                                </span>
                            </Link>
                            <button
                                type="button"
                                onClick={handleLogout}
                                title={translate("account.logout") || "Sign Out"}
                                className="p-2 text-slate-400 hover:text-red-600 transition-colors rounded-lg hover:bg-red-50"
                            >
                                <LogOut size={16} />
                            </button>
                        </div>
                    ) : (
                        <Link
                            to="/login"
                            className="hidden sm:inline-flex items-center ml-2 px-4 py-2 text-xs font-semibold text-white bg-[#004687] hover:bg-[#003669] rounded-lg transition-colors shadow-card"
                        >
                            {translate("header.login")}
                        </Link>
                    )}

                    <button
                        type="button"
                        onClick={toggleMobileMenu}
                        aria-label="Toggle Menu"
                        className="md:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                    >
                        {isMobileMenuOpen ? <X size={22} /> : <Menu size={22} />}
                    </button>
                </div>
            </div>

            {/* Mobile Drawer */}
            {isMobileMenuOpen && (
                <div className="md:hidden bg-white border-t border-slate-200 shadow-modal animate-fade-up">
                    <div className="px-4 py-4 space-y-4">
                        <SearchBar placeholder={translate("header.search_placeholder")} />

                        <Navbar translate={translate} mobile onItemClick={closeMobileMenu} />

                        <div className="pt-3 border-t border-slate-200 flex items-center justify-between">
                            <LanguageSwitcher
                                settings={settings}
                                currentLang={language}
                                onLangChange={setLanguage}
                                currentCurr={currency}
                                onCurrChange={setCurrency}
                            />

                            {user ? (
                                <div className="flex items-center gap-2">
                                    <Link
                                        to="/my-account"
                                        onClick={closeMobileMenu}
                                        className="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg text-xs font-semibold text-slate-800"
                                    >
                                        <User size={15} className="text-[#004687]" />
                                        <span>{user.name}</span>
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={handleLogout}
                                        className="p-2 bg-red-50 rounded-lg text-red-600"
                                    >
                                        <LogOut size={16} />
                                    </button>
                                </div>
                            ) : (
                                <Link
                                    to="/login"
                                    onClick={closeMobileMenu}
                                    className="px-4 py-2 bg-[#004687] text-white text-xs font-semibold rounded-lg"
                                >
                                    {translate("header.login")}
                                </Link>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </header>
    )
}

export default Header
