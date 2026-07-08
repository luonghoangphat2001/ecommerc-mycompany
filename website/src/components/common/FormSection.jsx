import React from "react"
import IconBadge from "./IconBadge"

const SECTION_BASE = "bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-8 md:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.02)]"

const FormSection = ({ title, icon, iconColor = "blue", children, className = "" }) => {
    return (
        <section className={`${SECTION_BASE} ${className}`}>
            {title && (
                <div className="flex items-center gap-3 mb-8">
                    {icon && <IconBadge icon={icon} color={iconColor} />}
                    <h2 className="text-xl font-bold text-slate-900">{title}</h2>
                </div>
            )}
            {children}
        </section>
    )
}

export default FormSection
