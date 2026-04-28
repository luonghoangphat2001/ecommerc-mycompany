import React, { createContext } from "react"

const CardContext = createContext()

const SHADOWS = {
    none: "",
    sm: "shadow-[0_8px_30px_rgba(0,0,0,0.02)]",
    md: "shadow-[0_20px_50px_rgba(0,0,0,0.04)]",
    lg: "shadow-[0_20px_50px_rgba(0,0,0,0.05)]",
}

const CARD_BASE = "bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2.5rem] transition-all duration-300"

const Card = ({ children, shadow = "sm", overflow = "hidden", className = "" }) => {
    const shadowClass = SHADOWS[shadow] ?? SHADOWS.sm
    const overflowClass = overflow === "hidden" ? "overflow-hidden" : ""
    return (
        <CardContext.Provider value={{}}>
            <div className={`${CARD_BASE} ${shadowClass} ${overflowClass} ${className}`}>{children}</div>
        </CardContext.Provider>
    )
}

Card.Header = ({ children, className = "" }) => <div className={`p-8 border-b border-slate-100 ${className}`}>{children}</div>

Card.Body = ({ children, className = "" }) => <div className={`p-8 ${className}`}>{children}</div>

Card.Footer = ({ children, className = "" }) => <div className={`p-8 border-t border-slate-100 bg-slate-50/50 ${className}`}>{children}</div>

Card.Title = ({ children, className = "" }) => <h3 className={`text-xl font-bold text-slate-900 ${className}`}>{children}</h3>

export default Card
