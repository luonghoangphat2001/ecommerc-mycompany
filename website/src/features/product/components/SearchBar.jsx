import React, { useState } from "react"
import { useNavigate } from "react-router-dom"
import { Search } from "lucide-react"

const SearchBar = ({ placeholder }) => {
    const [query, setQuery] = useState("")
    const navigate = useNavigate()

    const handleSubmit = (e) => {
        e.preventDefault()
        const trimmed = query.trim()
        if (trimmed) {
            navigate(`/shop?search=${encodeURIComponent(trimmed)}`)
        } else {
            navigate("/shop")
        }
    }

    return (
        <form onSubmit={handleSubmit} className="relative group w-full">
            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <Search className="h-4 w-4 text-slate-400 group-focus-within:text-[#004687] transition-colors" />
            </div>
            <input
                type="search"
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                className="block w-full pl-10 pr-3.5 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50/80 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687] transition-all"
                placeholder={placeholder || "Search products, SKUs..."}
            />
        </form>
    )
}

export default SearchBar
