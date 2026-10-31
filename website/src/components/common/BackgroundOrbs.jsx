import React from "react"

/**
 * Architectural background grid replacing legacy AI-style blurry gradient orbs.
 * Renders a restrained, barely-visible structural hairline grid.
 */
const BackgroundOrbs = () => {
    return (
        <div
            aria-hidden="true"
            className="pointer-events-none absolute inset-0 overflow-hidden"
            style={{
                backgroundImage:
                    "radial-gradient(rgba(9, 37, 62, 0.05) 1px, transparent 1px)",
                backgroundSize: "24px 24px",
            }}
        />
    )
}

export default BackgroundOrbs
