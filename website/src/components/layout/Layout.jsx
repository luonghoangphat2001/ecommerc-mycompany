import React, { useEffect } from "react"
import { Outlet } from "react-router-dom"
import { useQueryClient } from "@tanstack/react-query"
import Header from "./Header"
import Footer from "./Footer"
import MiniCart from "../../features/cart/components/MiniCart"
import useSettingsStore from "../../store/useSettingsStore"
import useHeader from "../../hooks/useHeader"

const Layout = () => {
    const queryClient = useQueryClient()
    const settings = useSettingsStore((state) => state.settings)
    const language = useSettingsStore((state) => state.language)
    const favicon = useSettingsStore((state) => state.getSetting("general.favicon"))
    const siteName = useSettingsStore(
        (state) => state.getSetting("general.site_name") || state.getSetting("general.store_name") || "NovaStore"
    )
    const { isCartOpen, closeCart } = useHeader()
    const customCss = settings?.custom?.custom_css || ""
    const customJs = settings?.custom?.custom_js || ""

    useEffect(() => {
        if (typeof document !== "undefined" && language) {
            document.documentElement.lang = language
        }
        queryClient.invalidateQueries()
    }, [language, queryClient])

    useEffect(() => {
        if (favicon) {
            let link = document.querySelector("link[rel~='icon']")
            if (!link) {
                link = document.createElement("link")
                link.rel = "icon"
                document.getElementsByTagName("head")[0].appendChild(link)
            }
            link.href = favicon
        }
    }, [favicon, siteName])

    useEffect(() => {
        let styleTag = document.getElementById("custom-settings-css")
        if (!styleTag) {
            styleTag = document.createElement("style")
            styleTag.id = "custom-settings-css"
            document.head.appendChild(styleTag)
        }
        styleTag.textContent = customCss || ""

        let scriptTag = document.getElementById("custom-settings-js")
        if (scriptTag) {
            scriptTag.remove()
        }

        if (customJs.trim()) {
            scriptTag = document.createElement("script")
            scriptTag.id = "custom-settings-js"
            scriptTag.type = "text/javascript"
            scriptTag.text = customJs
            document.body.appendChild(scriptTag)
        }

        return () => {
            const existingStyle = document.getElementById("custom-settings-css")
            if (existingStyle) {
                existingStyle.textContent = ""
            }
            const existingScript = document.getElementById("custom-settings-js")
            if (existingScript) {
                existingScript.remove()
            }
        }
    }, [customCss, customJs])

    return (
        <div className="min-h-screen bg-[#F8FAFC] text-slate-900 flex flex-col font-sans relative">
            <Header />

            <main className="flex-1 w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                <Outlet />
            </main>

            <Footer />

            <MiniCart isOpen={isCartOpen} onClose={closeCart} />
        </div>
    )
}

export default Layout
