import React from "react"

const INPUT_BASE = "w-full py-3.5 bg-white/50 border border-slate-200 rounded-2xl " + "focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 " + "transition-all placeholder:text-slate-400 " + "disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed"

const ICON_WRAPPER = "absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 " + "group-focus-within:text-blue-500 transition-colors pointer-events-none"

const FormInput = ({ label, name, type = "text", value, onChange, placeholder, required = false, disabled = false, icon: Icon, labelExtra, className = "" }) => {
    const paddingClass = Icon ? "pl-12 pr-4" : "px-4"

    return (
        <div className={className}>
            {label && (
                <div className="flex items-center justify-between mb-2 ml-1">
                    <label className="block text-sm font-semibold text-slate-700">
                        {label}
                        {required && <span className="text-red-500 ml-1">*</span>}
                    </label>
                    {labelExtra}
                </div>
            )}

            <div className="relative group">
                {Icon && (
                    <span className={ICON_WRAPPER}>
                        <Icon size={18} />
                    </span>
                )}
                <input type={type} name={name} value={value} onChange={onChange} placeholder={placeholder} required={required} disabled={disabled} className={`${INPUT_BASE} ${paddingClass}`} />
            </div>
        </div>
    )
}

export default FormInput
