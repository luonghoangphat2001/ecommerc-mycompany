import React from "react"
import { Link } from "react-router-dom"
import { ShoppingCart, AlertCircle } from "lucide-react"
import useCartStore from "../../cart/store/useCartStore"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"

const ProductCard = ({ product }) => {
    const addToCart = useCartStore((state) => state.addToCart)
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()

    const stock = product.stock || product.qty || 0
    const inventories = product.inventories || []
    const totalWarehouseStock = inventories.reduce(
        (sum, inv) => sum + (inv.stock_quantity || inv.pivot?.quantity || 0),
        0
    )
    const isAvailable = product.is_available !== false && (stock > 0 || totalWarehouseStock > 0)

    const handleAddToCart = (e) => {
        e.preventDefault()
        e.stopPropagation()
        if (isAvailable) {
            addToCart(product, 1)
        }
    }

    const imageSrc = product.image?.url || product.image || product.image_url
    const hasDiscount = product.is_on_sale
    const discountPercent =
        hasDiscount && product.price > 0
            ? Math.round((1 - product.effective_price / product.price) * 100)
            : 0

    return (
        <Link
            to={`/products/${product.slug || product.id}`}
            className={`group flex flex-col rounded-xl overflow-hidden transition-all duration-200 bg-white border border-slate-200/90 shadow-card hover:border-[#004687]/60 hover:shadow-card-hover hover:-translate-y-0.5 ${
                !isAvailable ? "opacity-80" : ""
            }`}
        >
            {/* Product Image */}
            <div className="relative aspect-[4/3] bg-slate-100 overflow-hidden border-b border-slate-100">
                <img
                    src={imageSrc || "/placeholder-product.jpg"}
                    alt={product.name}
                    loading="lazy"
                    className={`w-full h-full object-cover transition-transform duration-300 ease-out ${
                        isAvailable ? "group-hover:scale-[1.03]" : "grayscale"
                    }`}
                    onError={(e) => {
                        e.target.src = "/placeholder-product.jpg"
                    }}
                />

                {/* Top Left Badges */}
                <div className="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
                    {(product.featured || product.is_featured) && (
                        <span className="px-2 py-0.5 text-[11px] font-semibold bg-[#09253E] text-white rounded-md shadow-card">
                            {translate("product.featured") || "Featured"}
                        </span>
                    )}
                    {hasDiscount && discountPercent > 0 && (
                        <span className="px-2 py-0.5 text-[11px] font-bold bg-red-600 text-white rounded-md shadow-card tabular-nums">
                            -{discountPercent}%
                        </span>
                    )}
                </div>

                {/* Top Right Stock Badge if Out of Stock */}
                {!isAvailable && (
                    <div className="absolute top-2.5 right-2.5">
                        <span className="px-2 py-0.5 text-[11px] font-semibold bg-slate-900/85 text-white rounded-md">
                            Out of Stock
                        </span>
                    </div>
                )}
            </div>

            {/* Product Content */}
            <div className="flex flex-col flex-1 p-4 sm:p-5">
                {/* Category & Brand Meta */}
                <div className="flex items-center justify-between gap-2 mb-1.5">
                    {product.categories && product.categories.length > 0 ? (
                        <span className="text-[11px] text-[#004687] font-semibold uppercase tracking-wider truncate">
                            {product.categories[0].name}
                        </span>
                    ) : (
                        <span className="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">
                            {product.brand?.name || "Catalog"}
                        </span>
                    )}

                    <span
                        className={`inline-flex items-center gap-1 text-[11px] font-medium shrink-0 ${
                            isAvailable ? "text-emerald-700" : "text-red-600"
                        }`}
                    >
                        <span
                            className={`w-1.5 h-1.5 rounded-full ${
                                isAvailable ? "bg-emerald-500" : "bg-red-500"
                            }`}
                        />
                        {isAvailable ? "In Stock" : "Out of Stock"}
                    </span>
                </div>

                {/* Product Name */}
                <h3 className="font-semibold text-[#09253E] line-clamp-2 text-sm leading-snug mb-3 group-hover:text-[#004687] transition-colors">
                    {product.name}
                </h3>

                {/* Price & Action Footer */}
                <div className="mt-auto flex items-center justify-between gap-2 pt-3 border-t border-slate-100">
                    <div className="flex flex-col min-w-0">
                        <span className="font-bold text-base text-[#09253E] tabular-nums truncate">
                            {formatCurrency(product.effective_price || product.price)}
                        </span>
                        {hasDiscount && (
                            <span className="text-xs text-slate-400 line-through tabular-nums">
                                {formatCurrency(product.price)}
                            </span>
                        )}
                    </div>

                    <button
                        type="button"
                        onClick={handleAddToCart}
                        disabled={!isAvailable}
                        aria-label={isAvailable ? translate("product.add_to_cart") : "Out of Stock"}
                        title={isAvailable ? translate("product.add_to_cart") : "Out of Stock"}
                        className={`h-9 px-3 rounded-lg inline-flex items-center justify-center gap-1.5 text-xs font-semibold transition-colors shrink-0 ${
                            isAvailable
                                ? "bg-[#004687]/10 text-[#004687] hover:bg-[#004687] hover:text-white"
                                : "bg-slate-100 text-slate-400 cursor-not-allowed"
                        }`}
                    >
                        {isAvailable ? <ShoppingCart size={15} /> : <AlertCircle size={15} />}
                    </button>
                </div>
            </div>
        </Link>
    )
}

export default ProductCard
