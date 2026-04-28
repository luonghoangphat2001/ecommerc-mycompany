import React, { useEffect } from "react"
import { Outlet } from "react-router-dom"
import Header from "./Header"
import Footer from "./Footer"
import useSettingsStore from "../../store/useSettingsStore"

const Layout = () => {
    const favicon = useSettingsStore((state) => state.getSetting("general.favicon"))

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
    }, [favicon])

    return (
        <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans relative overflow-x-hidden">
            <div className="fixed top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-400 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>
            <div className="fixed bottom-[-20%] left-[-10%] w-[500px] h-[500px] bg-purple-400 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>

            <Header />

            <main className="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 z-10">
                <Outlet />
            </main>

            <Footer />
        </div>
    )
}

export default Layout
