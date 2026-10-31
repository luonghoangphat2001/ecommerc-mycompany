import React from "react"
import IconBadge from "./IconBadge"

const SECTION_BASE = "bg-white border border-slate-200/90 rounded-xl p-6 sm:p-7 shadow-card"

const FormSection = ({ title, icon, iconColor = "blue", children, className = "" }) => {
    return (
        <section className={`${SECTION_BASE} ${className}`}>
            {title && (
                <div className="flex items-center gap-3 pb-4 mb-6 border-b border-slate-100">
                    {icon && <IconBadge icon={icon} color={iconColor} size="sm" />}
                    <h2 className="text-base sm:text-lg font-bold text-slate-900 tracking-tight">{title}</h2>
                </div>
            )}
            {children}
        </section>
    )
}

export default FormSection
