import React, { useEffect } from "react"
import { Outlet } from "react-router-dom"
import Header from "./Header"
import Footer from "./Footer"
import MiniCart from "../../features/cart/components/MiniCart"
import useSettingsStore from "../../store/useSettingsStore"
import useHeader from "../../hooks/useHeader"

const Layout = () => {
    const settings = useSettingsStore((state) => state.settings)
    const favicon = useSettingsStore((state) => state.getSetting("general.favicon"))
    const siteName = useSettingsStore((state) => state.getSetting("general.site_name") || state.getSetting("general.store_name") || "NovaStore")
    const { isCartOpen, closeCart } = useHeader()
    const customCss = settings?.custom?.custom_css || ""
    const customJs = settings?.custom?.custom_js || ""

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

        document.title = siteName
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
        <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans relative overflow-x-hidden">
            <div className="fixed top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-400 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>
            <div className="fixed bottom-[-20%] left-[-10%] w-[500px] h-[500px] bg-purple-400 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>

            <Header />

            <main className="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 z-10">
                <Outlet />
            </main>

            <Footer />
            
            <MiniCart isOpen={isCartOpen} onClose={closeCart} />
        </div>
    )
}

export default Layout
