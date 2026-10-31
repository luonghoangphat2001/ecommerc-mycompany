import React from "react"
import { useLocation } from "react-router-dom"
import NavLink from "../common/NavLink"
import { useMenu } from "../../features/menu/hooks/useMenu"
import { ChevronDown } from "lucide-react"

const MenuItem = ({ item, currentPath, onItemClick }) => {
    const hasChildren = item.children && item.children.length > 0
    const targetUrl = item.url || `/${item.slug}`
    const isActive =
        targetUrl === "/"
            ? currentPath === "/"
            : currentPath === targetUrl || currentPath.startsWith(`${targetUrl}/`)

    if (hasChildren) {
        return (
            <div className="relative group">
                <button
                    type="button"
                    className={`flex items-center gap-1.5 py-2 text-sm font-semibold transition-colors ${
                        isActive ? "text-[#004687]" : "text-slate-700 hover:text-[#004687]"
                    }`}
                >
                    <span>{item.label}</span>
                    <ChevronDown size={15} className="text-slate-400 transition-transform duration-150 group-hover:rotate-180" />
                </button>
                <div className="absolute top-full left-0 pt-1.5 w-52 opacity-0 invisible translate-y-1 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-150 z-50">
                    <div className="bg-white rounded-xl shadow-modal border border-slate-200/90 py-1.5 overflow-hidden">
                        {item.children.map((child) => (
                            <NavLink
                                key={child.id}
                                to={child.url || `/${child.slug}`}
                                onClick={onItemClick}
                                className="block px-4 py-2 text-sm text-slate-600 hover:text-[#004687] hover:bg-slate-50 transition-colors"
                            >
                                {child.label}
                            </NavLink>
                        ))}
                    </div>
                </div>
            </div>
        )
    }

    return (
        <NavLink
            to={targetUrl}
            onClick={onItemClick}
            className={`relative py-2 text-sm font-semibold transition-colors ${
                isActive
                    ? "text-[#004687] after:content-[''] after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-[#004687] after:rounded-full"
                    : "text-slate-700 hover:text-[#004687]"
            }`}
        >
            {item.label}
        </NavLink>
    )
}

const Navbar = ({ translate, mobile = false, onItemClick }) => {
    const { menuItems } = useMenu("main-menu")
    const location = useLocation()

    const fallbackItems = [
        { id: 1, label: translate?.("header.home") || "Home", url: "/", children: [] },
        { id: 2, label: translate?.("header.shop") || "Catalog", url: "/shop", children: [] },
        { id: 3, label: translate?.("header.blog") || "Insights", url: "/posts", children: [] },
    ]

    const items = menuItems.length > 0 ? menuItems : fallbackItems

    if (mobile) {
        return (
            <nav className="flex flex-col space-y-1">
                {items.map((item) => {
                    const targetUrl = item.url || `/${item.slug}`
                    const isActive =
                        targetUrl === "/"
                            ? location.pathname === "/"
                            : location.pathname === targetUrl || location.pathname.startsWith(`${targetUrl}/`)
                    return (
                        <div key={item.id}>
                            <NavLink
                                to={targetUrl}
                                onClick={onItemClick}
                                className={`block px-3 py-2.5 rounded-lg text-sm font-semibold ${
                                    isActive
                                        ? "bg-[#004687]/10 text-[#004687]"
                                        : "text-slate-700 hover:bg-slate-100"
                                }`}
                            >
                                {item.label}
                            </NavLink>
                            {item.children?.length > 0 && (
                                <div className="pl-4 mt-1 space-y-1 border-l border-slate-200 ml-3">
                                    {item.children.map((child) => (
                                        <NavLink
                                            key={child.id}
                                            to={child.url || `/${child.slug}`}
                                            onClick={onItemClick}
                                            className="block px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-[#004687]"
                                        >
                                            {child.label}
                                        </NavLink>
                                    ))}
                                </div>
                            )}
                        </div>
                    )
                })}
            </nav>
        )
    }

    return (
        <nav className="hidden md:flex items-center gap-7">
            {items.map((item) => (
                <MenuItem key={item.id} item={item} currentPath={location.pathname} onItemClick={onItemClick} />
            ))}
        </nav>
    )
}

export default Navbar
