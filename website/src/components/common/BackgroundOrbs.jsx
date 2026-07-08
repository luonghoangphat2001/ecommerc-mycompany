import React from "react"

const PRESETS = {
    auth: [
        { className: "w-[600px] h-[600px] bg-blue-300 -top-20 -left-20", opacity: "opacity-10" },
        { className: "w-[500px] h-[500px] bg-purple-300 -bottom-20 -right-20", opacity: "opacity-10" },
    ],
    hero: [
        { className: "w-[600px] h-[600px] bg-blue-400 top-0 right-0 -mr-40 -mt-40 animate-pulse", opacity: "opacity-20" },
        { className: "w-[500px] h-[500px] bg-purple-400 bottom-0 left-0 -ml-40 -mb-40", opacity: "opacity-20" },
    ],
}

const ORB_BASE = "absolute rounded-full blur-[120px] pointer-events-none"

const BackgroundOrbs = ({ preset = "auth", orbs }) => {
    const list = orbs || PRESETS[preset] || PRESETS.auth

    return (
        <>
            {list.map((orb, i) => (
                <div key={i} className={`${ORB_BASE} ${orb.className} ${orb.opacity || "opacity-10"}`} />
            ))}
        </>
    )
}

export default BackgroundOrbs
