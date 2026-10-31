import React from "react"
import useSettingsStore from "../../../store/useSettingsStore"

const AboutUs = ({ content }) => {
    const translate = useSettingsStore((state) => state.translate)

    if (!content) return null

    return (
        <div className="max-w-4xl mx-auto py-12 px-4">
            <h2 className="text-2xl font-bold text-[#09253E] text-center mb-4 tracking-tight">
                {content.title || translate("header.about")}
            </h2>
            <p className="text-base text-slate-600 text-center leading-relaxed">
                {content.description || translate("footer.description")}
            </p>
        </div>
    )
}

export default AboutUs
