import React from "react"

const INPUT_BASE =
    "w-full py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687] transition-colors placeholder:text-slate-400 disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed"

const ICON_WRAPPER =
    "absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#004687] transition-colors pointer-events-none"

const FormInput = ({
    label,
    name,
    type = "text",
    value,
    onChange,
    placeholder,
    required = false,
    disabled = false,
    icon: Icon,
    labelExtra,
    className = "",
}) => {
    const paddingClass = Icon ? "pl-10 pr-3.5" : "px-3.5"

    return (
        <div className={className}>
            {label && (
                <div className="flex items-center justify-between mb-1.5">
                    <label htmlFor={name} className="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        {label}
                        {required && <span className="text-red-600 ml-1">*</span>}
                    </label>
                    {labelExtra}
                </div>
            )}

            <div className="relative group">
                {Icon && (
                    <span className={ICON_WRAPPER}>
                        <Icon size={16} />
                    </span>
                )}
                <input
                    id={name}
                    type={type}
                    name={name}
                    value={value}
                    onChange={onChange}
                    placeholder={placeholder}
                    required={required}
                    disabled={disabled}
                    className={`${INPUT_BASE} ${paddingClass}`}
                />
            </div>
        </div>
    )
}

export default FormInput
