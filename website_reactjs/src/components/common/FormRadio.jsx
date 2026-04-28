import React from "react"

const ACCENT_STYLES = {
    blue: { dot: "accent-blue-600", selected: "border-blue-600 bg-blue-50/30" },
    green: { dot: "accent-green-600", selected: "border-green-600 bg-green-50/30" },
}

const BASE_LABEL = "flex items-center gap-4 p-5 border-2 rounded-2xl cursor-pointer transition-all"
const IDLE_BORDER = "border-slate-200 hover:border-slate-300"

const FormRadio = ({ label, name, value, checked, onChange, accentColor = "blue", accessory, children, className = "" }) => {
    const accent = ACCENT_STYLES[accentColor] || ACCENT_STYLES.blue
    const borderClass = checked ? accent.selected : IDLE_BORDER

    return (
        <label className={`${BASE_LABEL} ${borderClass} ${className}`}>
            <input type="radio" name={name} value={value} checked={checked} onChange={onChange} className={`w-5 h-5 ${accent.dot}`} />

            <div className="flex-1">
                {label && <p className="font-bold text-slate-900">{label}</p>}
                {children}
            </div>
            {accessory}
        </label>
    )
}

export default FormRadio
