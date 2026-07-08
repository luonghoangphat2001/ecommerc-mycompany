import React from "react"

const ProductImage = ({ src, alt }) => {
    return (
        <div className="relative aspect-square bg-slate-100 rounded-2xl overflow-hidden shadow-inner">
            <img src={src || "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&q=80"} alt={alt} className="w-full h-full object-cover" />
        </div>
    )
}

export default ProductImage
