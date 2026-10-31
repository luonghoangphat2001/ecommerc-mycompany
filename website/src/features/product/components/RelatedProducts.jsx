import React from "react"
import { Link } from "react-router-dom"
import { useFormatters } from "../../../utils/useFormatters"

const RelatedProducts = ({ products, title, icon: Icon }) => {
    const { formatCurrency } = useFormatters()

    if (!products || products.length === 0) return null

    const getImageUrl = (product) => {
        if (product.image?.url) return product.image.url
        if (product.image) return product.image
        if (product.og_image) return product.og_image
        return "/placeholder-product.jpg"
    }

    return (
        <div className="mt-10 pt-8 border-t border-slate-200/80">
            <h3 className="text-base font-bold text-[#09253E] mb-4 flex items-center gap-2 tracking-tight">
                {Icon && <Icon size={18} className="text-[#004687]" />}
                <span>{title}</span>
            </h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-5">
                {products.map((item) => {
                    const product = item.product || item

                    return (
                        <Link
                            key={product.id}
                            to={`/products/${product.slug || product.id}`}
                            className="group flex flex-col bg-white rounded-xl border border-slate-200/90 overflow-hidden shadow-card hover:border-[#004687]/60 hover:shadow-card-hover transition-all"
                        >
                            <div className="aspect-[4/3] bg-slate-100 overflow-hidden border-b border-slate-100">
                                <img
                                    src={getImageUrl(product)}
                                    alt={product.name}
                                    className="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                                    onError={(e) => {
                                        e.target.src = "/placeholder-product.jpg"
                                    }}
                                />
                            </div>
                            <div className="p-3.5 flex flex-col flex-1">
                                <h4 className="text-xs sm:text-sm font-semibold text-[#09253E] line-clamp-2 mb-1.5 group-hover:text-[#004687] transition-colors">
                                    {product.name}
                                </h4>
                                <p className="mt-auto text-sm font-bold text-[#004687] tabular-nums">
                                    {formatCurrency(product.effective_price || product.price)}
                                </p>
                            </div>
                        </Link>
                    )
                })}
            </div>
        </div>
    )
}

export default RelatedProducts
