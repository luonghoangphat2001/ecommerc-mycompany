import React from "react"

const SIZES = {
    md: "text-xl sm:text-2xl",
    lg: "text-2xl sm:text-3xl",
    xl: "text-3xl sm:text-4xl",
}

const PageHeading = ({ size = "lg", as: Tag = "h1", children, className = "" }) => {
    const sizeClass = SIZES[size] || SIZES.lg
    return <Tag className={`${sizeClass} font-bold tracking-tight text-[#09253E] ${className}`}>{children}</Tag>
}

export default PageHeading
