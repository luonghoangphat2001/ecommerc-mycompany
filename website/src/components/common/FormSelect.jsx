import React from "react"
import { ChevronDown } from "lucide-react"

const SELECT_BASE =
    "w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687] transition-colors appearance-none pr-9"

const FormSelect = ({ label, name, value, onChange, options = [], placeholder = "", required = false, className = "" }) => {
    return (
        <div className={className}>
            {label && (
                <label htmlFor={name} className="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    {label}
                    {required && <span className="text-red-600 ml-1">*</span>}
                </label>
            )}

            <div className="relative">
                <select id={name} name={name} value={value} onChange={onChange} required={required} className={SELECT_BASE}>
                    {placeholder && <option value="">{placeholder}</option>}
                    {options.map(([key, optionLabel]) => (
                        <option key={key} value={key}>
                            {optionLabel}
                        </option>
                    ))}
                </select>
                <ChevronDown size={16} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            </div>
        </div>
    )
}

export default FormSelect
