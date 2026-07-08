import React from "react"

const Skeleton = ({ className, circle = false }) => {
    return <div className={`bg-slate-200 animate-pulse ${circle ? "rounded-full" : "rounded-lg"} ${className}`} />
}

export const ProductCardSkeleton = () => (
    <div className="flex flex-col bg-white/40 backdrop-blur-md rounded-2xl border border-white/60 shadow-sm overflow-hidden h-full">
        <Skeleton className="aspect-[4/5] rounded-none" />
        <div className="p-5 flex flex-col flex-1">
            <Skeleton className="h-4 w-3/4 mb-3" />
            <div className="flex gap-2 mb-4">
                <Skeleton className="h-3 w-12" />
                <Skeleton className="h-3 w-16" />
            </div>
            <div className="mt-auto flex items-center justify-between pt-4">
                <Skeleton className="h-6 w-20" />
                <Skeleton className="h-10 w-10 circle" />
            </div>
        </div>
    </div>
)

export default Skeleton
