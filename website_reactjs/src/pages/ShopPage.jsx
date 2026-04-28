import React from "react"
import { Link } from "react-router-dom"
import { PackageOpen } from "lucide-react"
import { useProducts } from "../features/product/hooks/useProducts"
import ProductCard from "../features/product/components/ProductCard"
import Error from "../components/common/Error"
import { ProductCardSkeleton } from "../components/common/Skeleton"
import useSettingsStore from "../store/useSettingsStore"

const ShopPage = () => {
    const { data: productsData, isLoading, isError, error } = useProducts()
    const translate = useSettingsStore((state) => state.translate)

    if (isLoading) {
        return (
            <div className="w-full">
                <div className="mb-10 text-center">
                    <div className="h-10 w-48 bg-slate-200 rounded-lg animate-pulse mx-auto mb-4" />
                    <div className="h-4 w-96 bg-slate-200 rounded-lg animate-pulse mx-auto" />
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                    {[...Array(8)].map((_, i) => (
                        <ProductCardSkeleton key={i} />
                    ))}
                </div>
            </div>
        )
    }
    if (isError) return <Error message={`${translate("shop.error")} ${error?.message}`} />

    const products = productsData?.data || []

    return (
        <div className="w-full">
            <div className="mb-10 text-center">
                <h1 className="text-4xl font-bold text-slate-800 tracking-tight mb-4">{translate("shop.title")}</h1>
                <p className="text-slate-500 max-w-2xl mx-auto">{translate("shop.subtitle")}</p>
            </div>

            {products.length === 0 ? (
                <div className="w-full py-20 flex flex-col items-center justify-center text-center">
                    <div className="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6">
                        <PackageOpen size={40} />
                    </div>
                    <h2 className="text-2xl font-bold text-slate-800 mb-2">{translate("shop.empty")}</h2>
                    <p className="text-slate-500 mb-8 max-w-xs mx-auto">{translate("shop.subtitle")}</p>
                    <Link to="/" className="bg-slate-900 text-white font-bold px-8 py-3.5 rounded-2xl hover:bg-blue-600 transition-all shadow-xl shadow-slate-200">
                        {translate("shop.continue_shopping")}
                    </Link>
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                    {products.map((product) => (
                        <ProductCard key={product.id} product={product} />
                    ))}
                </div>
            )}
        </div>
    )
}

export default ShopPage
