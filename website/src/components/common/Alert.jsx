import React from "react"

const VARIANTS = {
    error: "bg-red-50/90 text-red-700 border-red-200",
    success: "bg-emerald-50/90 text-emerald-700 border-emerald-200",
    info: "bg-sky-50/90 text-[#004687] border-sky-200",
    warning: "bg-amber-50/90 text-amber-800 border-amber-200",
}

const BASE = "px-4 py-3 text-sm rounded-lg border font-medium"

const Alert = ({ variant = "info", shake = false, className = "", children }) => {
    const variantClass = VARIANTS[variant] || VARIANTS.info
    const shakeClass = shake ? "animate-shake" : ""

    return <div className={`${BASE} ${variantClass} ${shakeClass} ${className}`}>{children}</div>
}

export default Alert
