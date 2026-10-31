import React from "react"

const ProductImage = ({ src, alt }) => {
    return (
        <div className="relative aspect-square bg-slate-100 rounded-xl overflow-hidden border border-slate-200/80">
            <img
                src={src || "/placeholder-product.jpg"}
                alt={alt}
                className="w-full h-full object-cover"
                onError={(e) => {
                    e.target.src = "/placeholder-product.jpg"
                }}
            />
        </div>
    )
}

export default ProductImage
