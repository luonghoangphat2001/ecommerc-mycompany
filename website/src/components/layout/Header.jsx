import React from "react"
import { Link } from "react-router-dom"
import { User, LogOut, Menu, X } from "lucide-react"
import useHeader from "../../hooks/useHeader"

import Logo from "../common/Logo"
import NavLink from "../common/NavLink"
import SearchBar from "../../features/product/components/SearchBar"
import CartIcon from "../../features/cart/components/CartIcon"
import LanguageSwitcher from "./LanguageSwitcher"
import Navbar from "./Navbar"

const Header = () => {
    const { user, _hasHydrated, cartCount, settings, language, currency, isMobileMenuOpen, setLanguage, setCurrency, translate, handleLogout, toggleMobileMenu, closeMobileMenu, toggleCart } = useHeader()

    return (
        <header className="h-20 bg-white/60 backdrop-blur-xl border-b border-white/40 shadow-[0_4px_30px_rgba(0,0,0,0.05)] sticky top-0 z-50 px-4 sm:px-6 lg:px-8">
            <div className="max-w-7xl mx-auto h-full flex items-center justify-between">
                <div className="flex items-center gap-8">
                    <Logo />
                    <Navbar translate={translate} />
                </div>

                <div className="flex-1 max-w-md px-8 hidden lg:block">
                    <SearchBar placeholder={translate("header.search_placeholder")} />
                </div>

                <div className="flex items-center gap-2 sm:gap-4">
                    <div className="hidden sm:flex items-center gap-2 mr-2">
                        <LanguageSwitcher settings={settings} currentLang={language} onLangChange={setLanguage} currentCurr={currency} onCurrChange={setCurrency} />
                    </div>

                    <CartIcon count={cartCount} onClick={toggleCart} />

                    {!_hasHydrated ? (
                        <div className="w-8 h-8 ml-4 bg-slate-100 rounded-full animate-pulse" />
                    ) : user ? (
                        <div className="hidden sm:flex items-center gap-3 pl-4 border-l border-slate-200">
                            <Link to="/my-account" className="flex items-center gap-2 group">
                                <div className="w-8 h-8 bg-gradient-to-tr from-blue-100 to-purple-100 rounded-full flex items-center justify-center text-blue-600 group-hover:scale-105 transition-transform">
                                    <User size={16} />
                                </div>
                                <span className="text-sm font-medium text-slate-700 group-hover:text-blue-600 transition-colors">{user.name}</span>
                            </Link>
                            <button onClick={handleLogout} className="p-2 text-slate-400 hover:text-red-500 transition-colors rounded-full hover:bg-red-50">
                                <LogOut size={18} />
                            </button>
                        </div>
                    ) : (
                        <Link to="/login" className="hidden sm:block ml-4 px-5 py-2 text-sm font-medium text-white bg-slate-900 rounded-full hover:bg-blue-600 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            {translate("header.login")}
                        </Link>
                    )}

                    <button onClick={toggleMobileMenu} className="md:hidden p-2 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                        {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
                    </button>
                </div>
            </div>

            {/* Mobile Navigation */}
            {isMobileMenuOpen && (
                <div className="md:hidden absolute top-full left-0 w-full bg-white/95 backdrop-blur-2xl border-b border-slate-200 shadow-2xl animate-in slide-in-from-top duration-300">
                    <div className="px-4 py-6 space-y-4">
                        <NavLink to="/" onClick={closeMobileMenu}>
                            {translate("header.home")}
                        </NavLink>
                        <NavLink to="/shop" onClick={closeMobileMenu}>
                            {translate("header.shop")}
                        </NavLink>

                        <div className="pt-4 border-t border-slate-100 flex items-center justify-between px-2">
                            <LanguageSwitcher settings={settings} currentLang={language} onLangChange={setLanguage} currentCurr={currency} onCurrChange={setCurrency} />

                            {user ? (
                                <div className="flex items-center gap-3">
                                    <Link to="/my-account" onClick={closeMobileMenu} className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                        <User size={20} />
                                    </Link>
                                    <button onClick={handleLogout} className="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center text-red-500">
                                        <LogOut size={20} />
                                    </button>
                                </div>
                            ) : (
                                <Link to="/login" onClick={closeMobileMenu} className="px-6 py-2 bg-slate-900 text-white font-bold rounded-xl">
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
