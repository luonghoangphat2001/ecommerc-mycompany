import React from "react"
import { Globe } from "lucide-react"
import { useQueryClient } from "@tanstack/react-query"
import useSettingsStore from "../../store/useSettingsStore"

const DEFAULT_LANGUAGES = [
    { code: "vi", name: "Tiếng Việt" },
    { code: "en", name: "English" },
]

const LanguageSwitcher = ({
    settings,
    currentLang,
    onLangChange,
    currentCurr,
    onCurrChange,
    variant = "light",
}) => {
    const queryClient = useQueryClient()
    const storeLang = useSettingsStore((state) => state.language)
    const storeSetLanguage = useSettingsStore((state) => state.setLanguage)
    const storeSetCurrency = useSettingsStore((state) => state.setCurrency)

    const isDark = variant === "dark"
    const selectClass = isDark
        ? "bg-transparent text-xs font-semibold text-slate-200 hover:text-white outline-none cursor-pointer uppercase"
        : "bg-transparent text-xs font-semibold text-slate-700 hover:text-[#004687] outline-none cursor-pointer uppercase"
    const optionClass = "bg-white text-slate-900"

    const languages =
        Array.isArray(settings?.languages) && settings.languages.length > 0
            ? settings.languages
            : DEFAULT_LANGUAGES

    const currencies = Array.isArray(settings?.currencies) ? settings.currencies : []

    const resolvedLang = currentLang || storeLang || "vi"
    const resolvedCurrCode =
        typeof currentCurr === "object"
            ? currentCurr?.code || "VND"
            : currentCurr || "VND"

    const handleLanguageSelect = (e) => {
        const nextLang = e.target.value
        if (onLangChange) {
            onLangChange(nextLang)
        } else {
            storeSetLanguage(nextLang)
        }
        queryClient.invalidateQueries()
    }

    const handleCurrencySelect = (e) => {
        const nextCurr = e.target.value
        if (onCurrChange) {
            onCurrChange(nextCurr)
        } else {
            storeSetCurrency(nextCurr)
        }
    }

    return (
        <div className="inline-flex items-center gap-2">
            <Globe size={13} className="text-slate-400" />
            <select
                aria-label="Language"
                value={resolvedLang}
                onChange={handleLanguageSelect}
                className={selectClass}
            >
                {languages.map((lang) => (
                    <option key={lang.code} value={lang.code} className={optionClass}>
                        {lang.code.toUpperCase()}
                    </option>
                ))}
            </select>

            {currencies.length > 0 && (
                <>
                    <span className={isDark ? "text-slate-600" : "text-slate-300"}>/</span>
                    <select
                        aria-label="Currency"
                        value={resolvedCurrCode}
                        onChange={handleCurrencySelect}
                        className={selectClass}
                    >
                        {currencies.map((curr) => (
                            <option key={curr.code} value={curr.code} className={optionClass}>
                                {curr.code}
                            </option>
                        ))}
                    </select>
                </>
            )}
        </div>
    )
}

export default LanguageSwitcher
