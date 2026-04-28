import React from "react"

const SIZES = {
    md: "text-2xl",
    lg: "text-3xl",
    xl: "text-4xl",
}

const PageHeading = ({ size = "lg", as: Tag = "h1", children, className = "" }) => {
    const sizeClass = SIZES[size] || SIZES.lg
    return <Tag className={`${sizeClass} font-black tracking-tight text-slate-900 ${className}`}>{children}</Tag>
}

export default PageHeading
