import React from "react"

const BASE = "flex items-center gap-4 p-5 border-2 border-slate-200 rounded-2xl cursor-pointer transition-all hover:border-slate-300"

const FormCheckbox = ({ label, name, checked, onChange, className = "" }) => {
    return (
        <label className={`${BASE} ${className}`}>
            <input type="checkbox" name={name} checked={checked} onChange={onChange} className="w-5 h-5 accent-blue-600" />
            <span className="font-medium text-slate-700">{label}</span>
        </label>
    )
}

export default FormCheckbox
