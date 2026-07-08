import React from "react"
import { ChevronDown } from "lucide-react"

const SELECT_BASE = "w-full px-4 py-3.5 bg-white/50 border border-slate-200 rounded-2xl " + "focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 " + "transition-all appearance-none pr-10"

const FormSelect = ({ label, name, value, onChange, options = [], placeholder = "", required = false, className = "" }) => {
    return (
        <div className={className}>
            {label && (
                <label className="block text-sm font-semibold text-slate-700 mb-2 ml-1">
                    {label}
                    {required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}

            <div className="relative">
                <select name={name} value={value} onChange={onChange} required={required} className={SELECT_BASE}>
                    {placeholder && <option value="">{placeholder}</option>}
                    {options.map(([key, optionLabel]) => (
                        <option key={key} value={key}>
                            {optionLabel}
                        </option>
                    ))}
                </select>
                <ChevronDown size={18} className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            </div>
        </div>
    )
}

export default FormSelect
