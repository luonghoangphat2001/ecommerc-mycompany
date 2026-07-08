import React from "react"

const LanguageSwitcher = ({ settings, currentLang, onLangChange, currentCurr, onCurrChange }) => (
    <div className="flex items-center gap-2">
        {settings?.languages && (
            <select value={currentLang} onChange={(e) => onLangChange(e.target.value)} className="bg-transparent text-sm font-medium text-slate-600 outline-none cursor-pointer uppercase">
                {settings.languages.map((lang) => (
                    <option key={lang.code} value={lang.code}>
                        {lang.code}
                    </option>
                ))}
            </select>
        )}

        <span className="text-slate-300">|</span>

        {settings?.currencies && (
            <select value={currentCurr} onChange={(e) => onCurrChange(e.target.value)} className="bg-transparent text-sm font-medium text-slate-600 outline-none cursor-pointer uppercase">
                {settings.currencies.map((curr) => (
                    <option key={curr.code} value={curr.code}>
                        {curr.code}
                    </option>
                ))}
            </select>
        )}
    </div>
)

export default LanguageSwitcher
