import React from "react"
import { ChevronLeft, ChevronRight } from "lucide-react"

const Pagination = ({ currentPage, totalPages, onPageChange }) => {
    if (totalPages <= 1) return null

    return (
        <nav aria-label="Pagination" className="flex items-center justify-center gap-1.5 mt-8 tabular-nums">
            <button
                onClick={() => onPageChange(currentPage - 1)}
                disabled={currentPage === 1}
                aria-label="Previous page"
                className="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            >
                <ChevronLeft size={18} />
            </button>

            {[...Array(totalPages)].map((_, i) => {
                const page = i + 1
                if (page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)) {
                    const isActive = currentPage === page
                    return (
                        <button
                            key={page}
                            onClick={() => onPageChange(page)}
                            aria-current={isActive ? "page" : undefined}
                            className={`w-9 h-9 text-sm flex items-center justify-center rounded-lg border transition-colors ${
                                isActive
                                    ? "bg-[#004687] text-white border-[#004687] font-semibold shadow-card"
                                    : "bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300"
                            }`}
                        >
                            {page}
                        </button>
                    )
                } else if (page === currentPage - 2 || page === currentPage + 2) {
                    return (
                        <span key={page} className="px-1.5 text-slate-400 text-sm">
                            …
                        </span>
                    )
                }
                return null
            })}

            <button
                onClick={() => onPageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
                aria-label="Next page"
                className="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            >
                <ChevronRight size={18} />
            </button>
        </nav>
    )
}

export default Pagination
