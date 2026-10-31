import React from "react"

const VARIANTS = {
    primary: "bg-[#004687] hover:bg-[#003669] text-white shadow-card",
    gold: "bg-[#FBAE17] hover:bg-[#e59c0d] text-[#09253E] shadow-card font-bold",
    blue: "bg-[#004687] hover:bg-[#003669] text-white shadow-card",
    secondary: "bg-slate-100 hover:bg-slate-200/80 text-slate-800 border border-slate-200/80",
    outline: "bg-white hover:bg-slate-50 text-slate-800 border border-slate-300",
    ghost: "bg-transparent hover:bg-slate-100 text-slate-700",
    danger: "bg-red-600 hover:bg-red-700 text-white shadow-card",
}

const SIZES = {
    sm: "py-1.5 px-3 text-xs",
    md: "py-2.5 px-4 text-sm",
    lg: "py-2.5 px-5 text-sm",
    xl: "py-3 px-6 text-sm sm:text-base",
    block: "py-2.5 px-4 w-full text-center text-sm font-semibold",
}

const BASE =
    "font-semibold rounded-lg transition-all duration-150 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center cursor-pointer select-none"

const Button = ({ as: Tag = "button", variant = "primary", size = "md", className = "", children, ...rest }) => {
    const variantClass = VARIANTS[variant] || VARIANTS.primary
    const sizeClass = SIZES[size] || SIZES.md
    const finalClass = `${BASE} ${variantClass} ${sizeClass} ${className}`

    if (Tag === "button" && !rest.type) rest.type = "button"

    return (
        <Tag className={finalClass} {...rest}>
            {children}
        </Tag>
    )
}

export default Button
