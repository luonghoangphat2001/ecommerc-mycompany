import React from "react"
import { Globe } from "lucide-react"

const LanguageSwitcher = ({ settings, currentLang, onLangChange, currentCurr, onCurrChange, variant = "light" }) => {
    const isDark = variant === "dark"
    const selectClass = isDark
        ? "bg-transparent text-xs font-semibold text-slate-200 hover:text-white outline-none cursor-pointer uppercase"
        : "bg-transparent text-xs font-semibold text-slate-700 hover:text-[#004687] outline-none cursor-pointer uppercase"
    const optionClass = "bg-white text-slate-900"

    return (
        <div className="inline-flex items-center gap-2">
            <Globe size={13} className={isDark ? "text-slate-400" : "text-slate-400"} />
            {settings?.languages && (
                <select
                    aria-label="Language"
                    value={currentLang}
                    onChange={(e) => onLangChange(e.target.value)}
                    className={selectClass}
                >
                    {settings.languages.map((lang) => (
                        <option key={lang.code} value={lang.code} className={optionClass}>
                            {lang.code}
                        </option>
                    ))}
                </select>
            )}

            {settings?.languages && settings?.currencies && (
                <span className={isDark ? "text-slate-600" : "text-slate-300"}>/</span>
            )}

            {settings?.currencies && (
                <select
                    aria-label="Currency"
                    value={currentCurr}
                    onChange={(e) => onCurrChange(e.target.value)}
                    className={selectClass}
                >
                    {settings.currencies.map((curr) => (
                        <option key={curr.code} value={curr.code} className={optionClass}>
                            {curr.code}
                        </option>
                    ))}
                </select>
            )}
        </div>
    )
}

export default LanguageSwitcher
