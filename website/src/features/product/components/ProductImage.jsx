import React from "react"
import { Package } from "lucide-react"

const ProductImage = ({ src, alt }) => {
    return (
        <div className="relative aspect-square bg-slate-100 rounded-xl overflow-hidden border border-slate-200/80 flex items-center justify-center text-slate-300">
            {src ? (
                <img
                    src={src}
                    alt={alt}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                        e.currentTarget.onerror = null
                        e.currentTarget.style.display = "none"
                        if (e.currentTarget.nextSibling) {
                            e.currentTarget.nextSibling.style.display = "flex"
                        }
                    }}
                />
            ) : null}
            <div className={`w-full h-full items-center justify-center ${src ? "hidden" : "flex"}`}>
                <Package size={48} />
            </div>
        </div>
    )
}

export default ProductImage
