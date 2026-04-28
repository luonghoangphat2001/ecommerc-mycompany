import React from "react"
import { Link } from "react-router-dom"
import { ShoppingCart } from "lucide-react"
import useCartStore from "../../cart/store/useCartStore"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"

const ProductCard = ({ product }) => {
    const addToCart = useCartStore((state) => state.addToCart)
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()

    const handleAddToCart = (e) => {
        e.preventDefault()
        e.stopPropagation()
        addToCart(product, 1)
    }

    const imageSrc = product.image?.url || product.image || product.image_url

    return (
        <Link to={`/products/${product.id}`} className="group flex flex-col rounded-2xl overflow-hidden transition-all duration-300 bg-white/40 backdrop-blur-md border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)]">
            {/* Product Image */}
            <div className="relative aspect-[4/5] bg-slate-100 overflow-hidden">
                <img
                    src={imageSrc || "/placeholder-product.jpg"}
                    alt={product.name}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    onError={(e) => {
                        e.target.src = "/placeholder-product.jpg"
                    }}
                />
                {product.is_featured && (
                    <div className="absolute top-3 left-3">
                        <span className="px-2.5 py-1 text-xs font-semibold bg-white/90 backdrop-blur-md text-slate-800 rounded-full shadow-sm">{translate("product.featured")}</span>
                    </div>
                )}
            </div>

            {/* Product Info */}
            <div className="flex flex-col flex-1 p-5">
                <h3 className="font-medium text-slate-800 line-clamp-1 text-sm leading-snug mb-1 group-hover:text-blue-600 transition-colors">{product.name}</h3>

                {/* Meta */}
                <div className="flex flex-col gap-1 mb-2">
                    {product.brand && <span className="text-[10px] uppercase tracking-wider font-bold text-slate-400">{product.brand.name}</span>}
                    {product.categories && product.categories.length > 0 && <span className="text-[10px] text-blue-500 font-medium">{product.categories[0].name}</span>}
                </div>

                {/* Price & Add to Cart */}
                <div className="mt-auto flex items-center justify-between pt-4">
                    <span className="font-bold text-slate-900">{formatCurrency(product.price)}</span>
                    <button onClick={handleAddToCart} title={translate("product.add_to_cart")} className="w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-sm hover:shadow-md bg-slate-50 text-slate-600 hover:bg-blue-600 hover:text-white">
                        <ShoppingCart size={18} />
                    </button>
                </div>
            </div>
        </Link>
    )
}

export default ProductCard
