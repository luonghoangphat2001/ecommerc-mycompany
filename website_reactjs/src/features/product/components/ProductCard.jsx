import React from "react"
import { Link } from "react-router-dom"
import { ShoppingCart, AlertCircle, CheckCircle2, Sparkles, Package } from "lucide-react"
import useCartStore from "../../cart/store/useCartStore"
import useSettingsStore from "../../../store/useSettingsStore"
import { useFormatters } from "../../../utils/useFormatters"

const ProductCard = ({ product }) => {
    const addToCart = useCartStore((state) => state.addToCart)
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()
    
    // Inventory info from API
    const stock = product.stock || product.qty || 0
    const inventories = product.inventories || []
    const totalWarehouseStock = inventories.reduce((sum, inv) => sum + (inv.stock_quantity || inv.pivot?.quantity || 0), 0)
    const isAvailable = product.is_available !== false && (stock > 0 || totalWarehouseStock > 0)

    const handleAddToCart = (e) => {
        e.preventDefault()
        e.stopPropagation()
        if (isAvailable) {
            addToCart(product, 1)
        }
    }

    const imageSrc = product.image?.url || product.image || product.image_url
    const hasDiscount = product.old_price && product.old_price > product.price
    const discountPercent = hasDiscount ? Math.round((1 - product.price / product.old_price) * 100) : 0

    return (
        <Link to={`/products/${product.slug || product.id}`} className={`group flex flex-col rounded-2xl overflow-hidden transition-all duration-300 bg-white/40 backdrop-blur-md border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] ${!isAvailable ? 'opacity-75' : ''}`}>
            {/* Product Image */}
            <div className="relative aspect-[4/5] bg-slate-100 overflow-hidden">
                <img
                    src={imageSrc || "/placeholder-product.jpg"}
                    alt={product.name}
                    className={`w-full h-full object-cover transition-transform duration-500 ${isAvailable ? 'group-hover:scale-105' : 'grayscale'}`}
                    onError={(e) => {
                        e.target.src = "/placeholder-product.jpg"
                    }}
                />
                
                {/* Badges */}
                <div className="absolute top-3 left-3 flex flex-col gap-2">
                    {product.featured && (
                        <span className="px-2.5 py-1 text-xs font-semibold bg-white/90 backdrop-blur-md text-slate-800 rounded-full shadow-sm flex items-center gap-1">
                            <Sparkles size={12} />
                            {translate("product.featured")}
                        </span>
                    )}
                    {hasDiscount && discountPercent > 0 && (
                        <span className="px-2.5 py-1 text-xs font-bold bg-red-500 text-white rounded-full shadow-sm">
                            -{discountPercent}%
                        </span>
                    )}
                </div>
                
                {/* Stock Status Badge */}
                <div className="absolute top-3 right-3">
                    {!isAvailable ? (
                        <span className="px-2.5 py-1 text-xs font-semibold bg-slate-800/90 text-white rounded-full">
                            Hết hàng
                        </span>
                    ) : null}
                </div>
            </div>

            {/* Product Info */}
            <div className="flex flex-col flex-1 p-5">
                {/* Category */}
                {product.categories && product.categories.length > 0 && (
                    <span className="text-[10px] text-blue-500 font-medium mb-1">
                        {product.categories[0].name}
                    </span>
                )}
                
                {/* Product Name */}
                <h3 className="font-medium text-slate-800 line-clamp-2 text-sm leading-snug mb-2 group-hover:text-blue-600 transition-colors">
                    {product.name}
                </h3>

                {/* Brand & Inventory */}
                <div className="flex flex-col gap-1 mb-2">
                    {product.brand && (
                        <span className="text-[10px] uppercase tracking-wider font-bold text-slate-400">
                            {product.brand.name}
                        </span>
                    )}
                    
                    {/* Inventory Info */}
                    {isAvailable && (
                        <div className="flex items-center gap-1 text-xs text-green-600">
                            <Package size={12} />
                            <span>Còn hàng</span>
                        </div>
                    )}
                    {!isAvailable && (
                        <div className="flex items-center gap-1 text-xs text-red-500">
                            <AlertCircle size={12} />
                            <span>Hết hàng</span>
                        </div>
                    )}
                </div>

                {/* Price & Add to Cart */}
                <div className="mt-auto flex items-center justify-between pt-4 border-t border-slate-100">
                    <div className="flex flex-col">
                        <span className="font-bold text-slate-900">{formatCurrency(product.price)}</span>
                        {hasDiscount && (
                            <span className="text-xs text-slate-400 line-through">
                                {formatCurrency(product.old_price)}
                            </span>
                        )}
                    </div>
                    
                    <button 
                        onClick={handleAddToCart} 
                        disabled={!isAvailable}
                        title={isAvailable ? translate("product.add_to_cart") : "Hết hàng"}
                        className={`w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-sm ${
                            isAvailable 
                                ? 'bg-slate-50 text-slate-600 hover:bg-blue-600 hover:text-white hover:shadow-md' 
                                : 'bg-slate-100 text-slate-400 cursor-not-allowed'
                        }`}
                    >
                        {isAvailable ? <ShoppingCart size={18} /> : <AlertCircle size={18} />}
                    </button>
                </div>
            </div>
        </Link>
    )
}

export default ProductCard
