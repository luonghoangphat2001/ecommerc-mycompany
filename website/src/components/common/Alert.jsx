import React from "react"

const VARIANTS = {
    error: "bg-red-50 text-red-600 border-red-100",
    success: "bg-green-50 text-green-600 border-green-100",
    info: "bg-blue-50 text-blue-600 border-blue-100",
    warning: "bg-amber-50 text-amber-600 border-amber-100",
}

const BASE = "p-4 text-sm rounded-2xl border font-medium"

const Alert = ({ variant = "info", shake = false, className = "", children }) => {
    const variantClass = VARIANTS[variant] || VARIANTS.info
    const shakeClass = shake ? "animate-shake" : ""

    return <div className={`${BASE} ${variantClass} ${shakeClass} ${className}`}>{children}</div>
}

export default Alert
