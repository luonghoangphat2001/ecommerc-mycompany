import React from "react"
import NavLink from "../common/NavLink"

const Navbar = ({ translate }) => (
    <nav className="hidden md:flex items-center gap-6">
        <NavLink to="/" className="nav-link">
            {translate("header.home")}
        </NavLink>
        <NavLink to="/shop" className="nav-link">
            {translate("header.shop")}
        </NavLink>
    </nav>
)

export default Navbar
