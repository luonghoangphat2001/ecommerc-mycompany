import React from "react"
import { Link } from "react-router-dom"
import useSettingsStore from "../../store/useSettingsStore"

const Logo = () => {
    const logo = useSettingsStore((state) => state.getSetting("general.logo"))
    const storeName = useSettingsStore((state) => state.getSetting("general.site_name") || state.getSetting("general.store_name") || "NovaStore")

    return (
        <Link to="/" className="flex items-center gap-2 hover:opacity-90 transition-opacity">
            {logo ? (
                <img src={logo} alt={storeName} className="h-8 w-auto object-contain" />
            ) : (
                <div className="flex items-center gap-2.5">
                    <div className="w-8 h-8 rounded-lg bg-[#004687] flex items-center justify-center text-white font-extrabold text-base shadow-xs">
                        <span className="text-[#FBAE17]">{storeName.charAt(0) || "N"}</span>
                    </div>
                    <span className="text-xl font-extrabold text-[#09253E] tracking-tight">
                        {storeName}
                    </span>
                </div>
            )}
        </Link>
    )
}

export default Logo
