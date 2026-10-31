import React from "react"

const BASE =
    "flex items-center gap-3 p-3.5 bg-white border border-slate-200 rounded-lg cursor-pointer transition-colors hover:border-slate-300 hover:bg-slate-50/50"

const FormCheckbox = ({ label, name, checked, onChange, className = "" }) => {
    return (
        <label className={`${BASE} ${className}`}>
            <input
                type="checkbox"
                name={name}
                checked={checked}
                onChange={onChange}
                className="w-4 h-4 rounded border-slate-300 accent-[#004687] cursor-pointer"
            />
            <span className="text-sm font-medium text-slate-700 select-none">{label}</span>
        </label>
    )
}

export default FormCheckbox
