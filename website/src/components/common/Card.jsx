import React, { createContext } from "react"

const CardContext = createContext()

const SHADOWS = {
    none: "",
    sm: "shadow-card",
    md: "shadow-sm",
    lg: "shadow-card-hover",
}

const CARD_BASE = "bg-white border border-slate-200/90 rounded-xl transition-all duration-200"

const Card = ({ children, shadow = "sm", overflow = "hidden", className = "" }) => {
    const shadowClass = SHADOWS[shadow] ?? SHADOWS.sm
    const overflowClass = overflow === "hidden" ? "overflow-hidden" : ""
    return (
        <CardContext.Provider value={{}}>
            <div className={`${CARD_BASE} ${shadowClass} ${overflowClass} ${className}`}>{children}</div>
        </CardContext.Provider>
    )
}

Card.Header = ({ children, className = "" }) => (
    <div className={`px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 ${className}`}>{children}</div>
)

Card.Body = ({ children, className = "" }) => <div className={`p-5 sm:p-6 ${className}`}>{children}</div>

Card.Footer = ({ children, className = "" }) => (
    <div className={`px-5 py-4 sm:px-6 sm:py-4 border-t border-slate-100 bg-slate-50/60 ${className}`}>{children}</div>
)

Card.Title = ({ children, className = "" }) => (
    <h3 className={`text-base sm:text-lg font-bold text-slate-900 tracking-tight ${className}`}>{children}</h3>
)

export default Card
