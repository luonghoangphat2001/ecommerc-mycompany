import React from "react"

const ACCENT_STYLES = {
    blue: {
        dot: "accent-[#004687]",
        selected: "border-[#004687] bg-[#004687]/[0.03] ring-1 ring-[#004687]",
    },
    green: {
        dot: "accent-emerald-600",
        selected: "border-emerald-600 bg-emerald-50/40 ring-1 ring-emerald-600",
    },
}

const BASE_LABEL = "flex items-center gap-3.5 p-4 border rounded-xl cursor-pointer transition-all bg-white"
const IDLE_BORDER = "border-slate-200 hover:border-slate-300 hover:bg-slate-50/40"

const FormRadio = ({ label, name, value, checked, onChange, accentColor = "blue", accessory, children, className = "" }) => {
    const accent = ACCENT_STYLES[accentColor] || ACCENT_STYLES.blue
    const borderClass = checked ? accent.selected : IDLE_BORDER

    return (
        <label className={`${BASE_LABEL} ${borderClass} ${className}`}>
            <input
                type="radio"
                name={name}
                value={value}
                checked={checked}
                onChange={onChange}
                className={`w-4 h-4 flex-shrink-0 ${accent.dot}`}
            />

            <div className="flex-1 min-w-0">
                {label && <p className="text-sm font-semibold text-slate-900">{label}</p>}
                {children}
            </div>
            {accessory}
        </label>
    )
}

export default FormRadio
