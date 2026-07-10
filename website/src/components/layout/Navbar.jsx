import React from "react"
import NavLink from "../common/NavLink"
import { useMenu } from "../../features/menu/hooks/useMenu"
import { ChevronDown } from "lucide-react"

/**
 * Recursive component to render menu items with children
 */
const MenuItem = ({ item, translate }) => {
    const hasChildren = item.children && item.children.length > 0

    if (hasChildren) {
        return (
            <div className="relative group">
                <button className="flex items-center gap-1 nav-link py-2">
                    {item.label}
                    <ChevronDown size={16} className="transition-transform group-hover:rotate-180" />
                </button>
                <div className="absolute top-full left-0 mt-1 w-56 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 py-2">
                    {item.children.map((child) => (
                        <NavLink
                            key={child.id}
                            to={child.url || `/${child.slug}`}
                            className="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-colors"
                        >
                            {child.label}
                        </NavLink>
                    ))}
                </div>
            </div>
        )
    }

    return (
        <NavLink to={item.url || `/${item.slug}`} className="nav-link">
            {item.label}
        </NavLink>
    )
}

const Navbar = ({ translate }) => {
    const { menuItems } = useMenu('main-menu')

    // Fallback menu items if API fails or loading
    const fallbackItems = [
        { id: 1, label: translate?.("header.home") || "Trang chủ", url: "/", children: [] },
        { id: 2, label: translate?.("header.shop") || "Cửa hàng", url: "/shop", children: [] },
    ]

    const items = menuItems.length > 0 ? menuItems : fallbackItems

    return (
        <nav className="hidden md:flex items-center gap-6">
            {items.map((item) => (
                <MenuItem key={item.id} item={item} translate={translate} />
            ))}
        </nav>
    )
}

export default Navbar
