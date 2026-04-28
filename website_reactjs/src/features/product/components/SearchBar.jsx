import React from "react"
import { Search } from "lucide-react"

const SearchBar = ({ placeholder }) => (
    <div className="relative group w-full">
        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search className="h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
        </div>
        <input type="text" className="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-full leading-5 bg-white/50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm" placeholder={placeholder} />
    </div>
)

export default SearchBar
