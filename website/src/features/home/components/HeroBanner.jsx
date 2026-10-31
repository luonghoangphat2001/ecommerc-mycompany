import React from "react"
import useSettingsStore from "../../../store/useSettingsStore"

const HeroBanner = ({ content }) => {
    const translate = useSettingsStore((state) => state.translate)

    if (!content) return null

    return (
        <div className="relative py-16 px-6 bg-[#09253E] rounded-xl border border-slate-800 flex items-center justify-center">
            <div className="text-center text-white max-w-2xl">
                <h1 className="text-3xl md:text-4xl font-bold mb-3 tracking-tight">
                    {content.title || translate("home.hero.title_1")}
                </h1>
                <p className="text-base md:text-lg text-slate-300 mb-6">
                    {content.subtitle || translate("home.hero.subtitle")}
                </p>
                <button
                    type="button"
                    className="bg-[#FBAE17] text-[#09253E] px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-[#e59c0d] transition-colors"
                >
                    {translate("home.hero.cta_primary")}
                </button>
            </div>
        </div>
    )
}

export default HeroBanner
