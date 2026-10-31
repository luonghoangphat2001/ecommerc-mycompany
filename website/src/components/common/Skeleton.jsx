import React from "react"

const Skeleton = ({ className = "", circle = false }) => {
    return <div className={`bg-slate-200/70 animate-pulse ${circle ? "rounded-full" : "rounded-md"} ${className}`} />
}

export const ProductCardSkeleton = () => (
    <div className="flex flex-col bg-white rounded-xl border border-slate-200/90 shadow-card overflow-hidden h-full">
        <Skeleton className="aspect-[4/3] rounded-none" />
        <div className="p-4 flex flex-col flex-1">
            <Skeleton className="h-3 w-1/3 mb-2" />
            <Skeleton className="h-4 w-3/4 mb-3" />
            <div className="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                <Skeleton className="h-5 w-20" />
                <Skeleton className="h-8 w-20 rounded-lg" />
            </div>
        </div>
    </div>
)

export default Skeleton
