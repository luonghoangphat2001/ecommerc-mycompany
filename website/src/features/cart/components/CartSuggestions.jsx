import React from "react"
import useSettingsStore from "../../../store/useSettingsStore"
import { useCartSuggestions } from "../hooks/useCartSuggestions"
import ProductCard from "../../product/components/ProductCard"

const CartSuggestions = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { data: suggestions, isLoading } = useCartSuggestions()

    if (!suggestions || suggestions.length === 0) return null

    return (
        <div className="mt-10 pt-8 border-t border-slate-200/80">
            <h2 className="text-lg font-bold text-[#09253E] mb-4 tracking-tight">
                {translate("cart.suggestions") || "Recommended for Your Order"}
            </h2>

            {isLoading ? (
                <div className="text-center text-slate-500 text-sm py-4">{translate("common.loading")}</div>
            ) : (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
                    {suggestions.map((product) => (
                        <ProductCard key={product.id} product={product} />
                    ))}
                </div>
            )}
        </div>
    )
}

export default CartSuggestions
