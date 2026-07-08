import React from "react"
import { Link } from "react-router-dom"
import useSettingsStore from "../../store/useSettingsStore"

const Logo = () => {
    const logo = useSettingsStore((state) => state.getSetting("general.logo"))
    const storeName = useSettingsStore((state) => state.getSetting("general.store_name")) || "NovaStore"

    return (
        <Link to="/" className="flex items-center gap-2 hover:opacity-80 transition-opacity">
            {logo ? <img src={logo} alt={storeName} className="h-8 w-auto object-contain" /> : <span className="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 tracking-tight">{storeName}</span>}
        </Link>
    )
}

export default Logo
