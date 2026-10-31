import React from "react"
import { Link } from "react-router-dom"

const NavLink = ({ to, children, onClick, className = "" }) => (
    <Link
        to={to}
        onClick={onClick}
        className={`text-sm font-medium text-slate-600 hover:text-[#004687] transition-colors ${className}`}
    >
        {children}
    </Link>
)

export default NavLink
