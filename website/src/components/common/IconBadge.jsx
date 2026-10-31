import React from "react"

const COLOR_MAP = {
    blue: "bg-[#004687]/10 text-[#004687]",
    orange: "bg-amber-50 text-amber-700 border border-amber-200/60",
    purple: "bg-slate-100 text-[#09253E]",
    green: "bg-emerald-50 text-emerald-700 border border-emerald-200/60",
    indigo: "bg-sky-50 text-sky-700 border border-sky-200/60",
    red: "bg-red-50 text-red-600 border border-red-200/60",
    amber: "bg-amber-50 text-amber-700 border border-amber-200/60",
    slate: "bg-slate-100 text-slate-600 border border-slate-200/60",
    teal: "bg-teal-50 text-teal-700 border border-teal-200/60",
}

const SIZES = {
    sm: { box: "w-8 h-8 rounded-lg", icon: 16 },
    md: { box: "w-10 h-10 rounded-lg", icon: 20 },
    lg: { box: "w-12 h-12 rounded-xl", icon: 24 },
    xl: { box: "w-16 h-16 rounded-xl", icon: 30 },
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
