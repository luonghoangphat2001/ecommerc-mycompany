import React from "react"

const COLOR_MAP = {
    blue: "bg-blue-50 text-blue-600",
    orange: "bg-orange-50 text-orange-600",
    purple: "bg-purple-50 text-purple-600",
    green: "bg-green-50 text-green-600",
    indigo: "bg-indigo-50 text-indigo-600",
    red: "bg-red-50 text-red-600",
    amber: "bg-amber-50 text-amber-600",
    slate: "bg-slate-100 text-slate-500",
    teal: "bg-teal-50 text-teal-600",
}

const SIZES = {
    sm: { box: "w-8 h-8 rounded-lg", icon: 16 },
    md: { box: "w-10 h-10 rounded-xl", icon: 22 },
    lg: { box: "w-14 h-14 rounded-2xl", icon: 28 },
    xl: { box: "w-24 h-24 rounded-full", icon: 40 },
}

const IconBadge = ({ icon: Icon, color = "blue", size = "md", className = "" }) => {
    const colorClass = COLOR_MAP[color] || COLOR_MAP.blue
    const sizeConf = SIZES[size] || SIZES.md

    return (
        <div className={`${sizeConf.box} flex items-center justify-center flex-shrink-0 ${colorClass} ${className}`}>
            <Icon size={sizeConf.icon} />
        </div>
    )
}

export default IconBadge
