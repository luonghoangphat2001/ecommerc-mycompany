import React from "react"
import IconBadge from "./IconBadge"

const EmptyState = ({ icon, iconColor = "slate", title, description, action, className = "" }) => {
    return (
        <div className={`w-full py-16 px-4 flex flex-col items-center justify-center text-center ${className}`}>
            {icon && <IconBadge icon={icon} color={iconColor} size="lg" className="mb-4" />}
            {title && <h2 className="text-xl font-bold text-slate-900 tracking-tight mb-1.5">{title}</h2>}
            {description && <p className="text-sm text-slate-500 mb-6 max-w-sm mx-auto leading-relaxed">{description}</p>}
            {action}
        </div>
    )
}

export default EmptyState
