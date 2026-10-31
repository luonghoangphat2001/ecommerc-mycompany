import React from "react"
import useSettingsStore from "../../../store/useSettingsStore"

const PostPagination = ({ currentPage, totalPages, onPageChange }) => {
    const translate = useSettingsStore((state) => state.translate)

    if (totalPages <= 1) return null

    const getPageNumbers = () => {
        const pages = []
        const maxVisible = 5

        let start = Math.max(1, currentPage - Math.floor(maxVisible / 2))
        let end = Math.min(totalPages, start + maxVisible - 1)

        if (end - start + 1 < maxVisible) {
            start = Math.max(1, end - maxVisible + 1)
        }

        for (let i = start; i <= end; i++) {
            pages.push(i)
        }

        return pages
    }

    return (
        <div className="flex items-center justify-center gap-1.5 mt-8 tabular-nums">
            <button
                type="button"
                className="px-3.5 py-2 border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                onClick={() => onPageChange(currentPage - 1)}
                disabled={currentPage === 1}
            >
                {translate("common.previous") || "Previous"}
            </button>

            {getPageNumbers().map((page) => (
                <button
                    key={page}
                    type="button"
                    className={`w-9 h-9 border rounded-lg text-xs font-semibold transition-colors ${
                        currentPage === page
                            ? "bg-[#004687] text-white border-[#004687]"
                            : "bg-white text-slate-700 border-slate-200 hover:bg-slate-50"
                    }`}
                    onClick={() => onPageChange(page)}
                >
                    {page}
                </button>
            ))}

            <button
                type="button"
                className="px-3.5 py-2 border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                onClick={() => onPageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
            >
                {translate("common.next") || "Next"}
            </button>
        </div>
    )
}

export default PostPagination
