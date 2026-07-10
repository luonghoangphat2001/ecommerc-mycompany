import React from "react"
import { ChevronLeft, ChevronRight } from "lucide-react"

const Pagination = ({ currentPage, totalPages, onPageChange }) => {
    if (totalPages <= 1) return null

    return (
        <div className="flex items-center justify-center space-x-2 mt-8">
            <button onClick={() => onPageChange(currentPage - 1)} disabled={currentPage === 1} className="p-2 rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <ChevronLeft size={20} />
            </button>

            {[...Array(totalPages)].map((_, i) => {
                const page = i + 1
                // Simple windowing logic
                if (page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)) {
                    return (
                        <button key={page} onClick={() => onPageChange(page)} className={`w-10 h-10 flex items-center justify-center rounded-lg border transition-colors ${currentPage === page ? "bg-slate-900 text-white border-slate-900 font-medium" : "bg-white text-slate-600 border-slate-200 hover:bg-slate-50"}`}>
                            {page}
                        </button>
                    )
                } else if (page === currentPage - 2 || page === currentPage + 2) {
                    return (
                        <span key={page} className="px-2 text-slate-400">
                            ...
                        </span>
                    )
                }
                return null
            })}

            <button onClick={() => onPageChange(currentPage + 1)} disabled={currentPage === totalPages} className="p-2 rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <ChevronRight size={20} />
            </button>
        </div>
    )
}

export default Pagination
