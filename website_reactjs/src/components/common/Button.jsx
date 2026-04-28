import React from "react"

const VARIANTS = {
    primary: "bg-slate-900 hover:bg-blue-600 text-white shadow-xl shadow-slate-200 hover:shadow-blue-200",
    blue: "bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-100",
    secondary: "bg-slate-200 hover:bg-slate-300 text-slate-800",
    ghost: "bg-transparent hover:bg-slate-100 text-slate-700",
    danger: "bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-100",
}

const SIZES = {
    sm: "py-2 px-4 text-sm",
    md: "py-3 px-6",
    lg: "py-3.5 px-8",
    xl: "py-4 px-10",
    block: "py-4 w-full text-center",
}

const BASE = "font-bold rounded-2xl transition-all active:scale-[0.98] transform disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none inline-flex items-center justify-center"

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
