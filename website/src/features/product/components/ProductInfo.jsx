import React from "react"
import { ShoppingCart, ShieldCheck, CheckCircle2, AlertCircle, Minus, Plus } from "lucide-react"
import { Button } from "../../../components/common"
import { useFormatters } from "../../../utils/useFormatters"

const ProductInfo = ({ product, stock, quantity, onIncrement, onDecrement, onAddToCart, translate }) => {
    const { formatCurrency, getLocalizedText } = useFormatters()
    const isAvailable = stock > 0
    const productName = getLocalizedText(product.name)
    const productDescription = getLocalizedText(product.description)

    return (
        <div className="flex flex-col">
            {/* Status & Featured Badges */}
            <div className="flex items-center flex-wrap gap-2 mb-3">
                {product.is_featured && (
                    <span className="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-[#004687] bg-[#004687]/10 rounded-md">
                        {translate("product.featured") || "Featured"}
                    </span>
                )}
                <span
                    className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-semibold rounded-md ${
                        isAvailable
                            ? "bg-emerald-50 text-emerald-700 border border-emerald-200/60"
                            : "bg-red-50 text-red-700 border border-red-200/60"
                    }`}
                >
                    {isAvailable ? <CheckCircle2 size={13} /> : <AlertCircle size={13} />}
                    <span className="tabular-nums">
                        {isAvailable
                            ? translate("product.in_stock_units", { count: stock }) || `In Stock (${stock} units)`
                            : translate("product.out_of_stock") || "Out of Stock"}
                    </span>
                </span>
            </div>

            {/* Product Title */}
            <h1 className="text-2xl sm:text-3xl font-bold text-[#09253E] mb-3 tracking-tight leading-snug">
                {productName}
            </h1>

            {/* Pricing Block */}
            <div className="flex items-baseline gap-3 pb-5 mb-5 border-b border-slate-200/80 tabular-nums">
                <span className="text-2xl sm:text-3xl font-extrabold text-[#004687]">
                    {formatCurrency(product.effective_price || product.price)}
                </span>
                {product.is_on_sale && (
                    <span className="text-base text-slate-400 line-through font-medium">
                        {formatCurrency(product.price)}
                    </span>
                )}
            </div>

            {/* Product Description */}
            <div className="text-sm text-slate-600 mb-6 leading-relaxed">
                <p>{productDescription || translate("product.no_description")}</p>
            </div>

            {/* Specifications Meta Table */}
            {(product.brand || (product.categories && product.categories.length > 0) || product.sku) && (
                <div className="flex flex-col gap-2.5 mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                    {product.sku && (
                        <div className="flex items-center justify-between">
                            <span className="text-slate-500 font-medium">{translate("product.sku") || "SKU"}:</span>
                            <span className="text-slate-900 font-mono font-semibold">{product.sku}</span>
                        </div>
                    )}
                    {product.brand && (
                        <div className="flex items-center justify-between">
                            <span className="text-slate-500 font-medium">{translate("product.brand")}:</span>
                            <span className="text-slate-900 font-semibold">{getLocalizedText(product.brand.name)}</span>
                        </div>
                    )}
                    {product.categories && product.categories.length > 0 && (
                        <div className="flex items-center justify-between gap-2">
                            <span className="text-slate-500 font-medium">{translate("product.categories")}:</span>
                            <div className="flex flex-wrap justify-end gap-1.5">
                                {product.categories.map((cat) => (
                                    <span
                                        key={cat.id}
                                        className="font-semibold bg-white border border-slate-200 text-slate-700 px-2 py-0.5 rounded"
                                    >
                                        {getLocalizedText(cat.name)}
                                    </span>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Quantity & Add to Cart Row */}
            <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-5">
                <div className="inline-flex items-center justify-between bg-white rounded-lg border border-slate-300 h-11 px-1 shrink-0">
                    <button
                        type="button"
                        onClick={onDecrement}
                        aria-label="Decrease quantity"
                        className="w-9 h-9 flex items-center justify-center text-slate-600 hover:text-[#004687] hover:bg-slate-100 rounded-md transition-colors"
                    >
                        <Minus size={15} />
                    </button>
                    <span className="w-10 text-center text-sm font-bold text-slate-900 tabular-nums">
                        {quantity}
                    </span>
                    <button
                        type="button"
                        onClick={onIncrement}
                        aria-label="Increase quantity"
                        className="w-9 h-9 flex items-center justify-center text-slate-600 hover:text-[#004687] hover:bg-slate-100 rounded-md transition-colors"
                    >
                        <Plus size={15} />
                    </button>
                </div>

                <Button
                    onClick={() => onAddToCart()}
                    disabled={!isAvailable}
                    variant="primary"
                    size="block"
                    className="h-11 gap-2.5 flex-1"
                >
                    <ShoppingCart size={18} />
                    <span>
                        {isAvailable
                            ? translate("product.add_to_cart")
                            : translate("product.out_of_stock") || "Out of Stock"}
                    </span>
                </Button>
            </div>

            {/* Service Guarantee Note */}
            <div className="flex items-center gap-2 text-xs text-slate-500 pt-2">
                <ShieldCheck size={15} className="text-[#004687] shrink-0" />
                <span>
                    {translate("product.warranty_note") ||
                        "Official warranty included • Inspected before dispatch"}
                </span>
            </div>
        </div>
    )
}

export default ProductInfo
