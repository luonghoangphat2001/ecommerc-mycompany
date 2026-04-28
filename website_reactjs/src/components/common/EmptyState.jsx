import React from "react"
import IconBadge from "./IconBadge"

const EmptyState = ({ icon, iconColor = "slate", title, description, action, className = "" }) => {
    return (
        <div className={`w-full py-20 flex flex-col items-center justify-center text-center ${className}`}>
            {icon && <IconBadge icon={icon} color={iconColor} size="xl" className="mb-6" />}
            {title && <h2 className="text-2xl font-bold text-slate-800 mb-2">{title}</h2>}
            {description && <p className="text-slate-500 mb-8 max-w-xs mx-auto">{description}</p>}
            {action}
        </div>
    )
}

export default EmptyState
