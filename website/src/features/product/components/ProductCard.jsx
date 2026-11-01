import React from "react"
import { Link } from "react-router-dom"
import { ShoppingCart, AlertCircle, Package } from "lucide-react"
import useCartStore from "../../cart/store/useCartStore"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"

const ProductCard = ({ product }) => {
    const addToCart = useCartStore((state) => state.addToCart)
    const translate = useSettingsStore((state) => state.translate)
    const manageStock = useSettingsStore((state) => state.settings?.inventory?.manage_stock ?? true)
    const catalogMode = useSettingsStore((state) => state.settings?.ecommerce?.catalog_mode ?? false)
    const ecommerceEnabled = useSettingsStore((state) => state.settings?.ecommerce?.enabled ?? true)
    const { formatCurrency, getLocalizedText } = useFormatters()

    const stock = product.stock || product.qty || 0
    const inventories = product.inventories || []
    const totalWarehouseStock = inventories.reduce(
        (sum, inv) => sum + (inv.stock_quantity || inv.pivot?.quantity || 0),
        0
    )
    const isStockAvailable = !manageStock || (stock > 0 || totalWarehouseStock > 0 || product.is_available === true)
    const isAvailable = product.is_available !== false && isStockAvailable
    const canAddToCart = isAvailable && !catalogMode && ecommerceEnabled

    const isVariable = product.type === "variable" || product.product_type === "variable"

    const handleAddToCart = (e) => {
        e.preventDefault()
        e.stopPropagation()
        if (!canAddToCart) return
        if (isVariable) {
            window.location.href = `/products/${product.slug || product.id}`
            return
        }
        addToCart(product, 1)
    }

    const imageSrc = product.image?.url || product.image || product.image_url
    const hasDiscount = product.is_on_sale
    const discountPercent =
        hasDiscount && product.price > 0
            ? Math.round((1 - product.effective_price / product.price) * 100)
            : 0
    const productName = getLocalizedText(product.name)

    return (
        <Link
            to={`/products/${product.slug || product.id}`}
            className={`group flex flex-col rounded-xl overflow-hidden transition-all duration-200 bg-white border border-slate-200/90 shadow-card hover:border-[#004687]/60 hover:shadow-card-hover hover:-translate-y-0.5 ${
                !isAvailable ? "opacity-80" : ""
            }`}
        >
            {/* Product Image */}
            <div className="relative aspect-[4/3] bg-slate-100 overflow-hidden border-b border-slate-100 flex items-center justify-center text-slate-300">
                {imageSrc ? (
                    <img
                        src={imageSrc}
                        alt={productName}
                        loading="lazy"
                        className={`w-full h-full object-cover transition-transform duration-300 ease-out ${
                            isAvailable ? "group-hover:scale-[1.03]" : "grayscale"
                        }`}
                        onError={(e) => {
                            e.currentTarget.onerror = null
                            e.currentTarget.style.display = "none"
                            if (e.currentTarget.nextSibling) {
                                e.currentTarget.nextSibling.style.display = "flex"
                            }
                        }}
                    />
                ) : null}
                <div className={`w-full h-full items-center justify-center ${imageSrc ? "hidden" : "flex"}`}>
                    <Package size={36} />
                </div>

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
                            {translate("product.out_of_stock") || "Out of Stock"}
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
                            {getLocalizedText(product.categories[0].name)}
                        </span>
                    ) : (
                        <span className="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">
                            {getLocalizedText(product.brand?.name) || translate("header.shop") || "Catalog"}
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
                        {isAvailable
                            ? translate("product.in_stock") || "In Stock"
                            : translate("product.out_of_stock") || "Out of Stock"}
                    </span>
                </div>

                {/* Product Name */}
                <h3 className="font-semibold text-[#09253E] line-clamp-2 text-sm leading-snug mb-3 group-hover:text-[#004687] transition-colors">
                    {productName}
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

                    {!catalogMode && ecommerceEnabled && (
                        <button
                            type="button"
                            onClick={handleAddToCart}
                            disabled={!canAddToCart}
                            aria-label={canAddToCart ? translate("product.add_to_cart") : translate("product.out_of_stock")}
                            title={canAddToCart ? translate("product.add_to_cart") : translate("product.out_of_stock")}
                            className={`h-9 px-3 rounded-lg inline-flex items-center justify-center gap-1.5 text-xs font-semibold transition-colors shrink-0 ${
                                canAddToCart
                                    ? "bg-[#004687]/10 text-[#004687] hover:bg-[#004687] hover:text-white"
                                    : "bg-slate-100 text-slate-400 cursor-not-allowed"
                            }`}
                        >
                            {canAddToCart ? <ShoppingCart size={15} /> : <AlertCircle size={15} />}
                        </button>
                    )}
                </div>
            </div>
        </Link>
    )
}

export default ProductCard
