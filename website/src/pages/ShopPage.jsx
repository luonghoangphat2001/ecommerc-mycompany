import React, { useState } from "react"
import { PackageOpen, Filter, Search } from "lucide-react"
import { useProducts } from "../features/product/hooks/useProducts"
import ProductCard from "../features/product/components/ProductCard"
import Error from "../components/common/Error"
import { ProductCardSkeleton } from "../components/common/Skeleton"
import useSettingsStore from "../store/useSettingsStore"
import Pagination from "../components/common/Pagination"
import FormInput from "../components/common/FormInput"

const ShopPage = () => {
    const translate = useSettingsStore((state) => state.translate)

    const [params, setParams] = useState({
        page: 1,
        per_page: 12,
        search: "",
        category_id: "",
        sort: "newest",
    })

    const { data: productsData, isLoading, isError, error } = useProducts(params)

    const handlePageChange = (page) => {
        setParams((prev) => ({ ...prev, page }))
        window.scrollTo({ top: 0, behavior: "smooth" })
    }

    const handleSearch = (e) => {
        e.preventDefault()
        setParams((prev) => ({ ...prev, page: 1 }))
    }

    if (isLoading && params.page === 1) {
        return (
            <div className="w-full">
                <div className="mb-10 text-center">
                    <div className="h-10 w-48 bg-slate-200 rounded-lg animate-pulse mx-auto mb-4" />
                    <div className="h-4 w-96 bg-slate-200 rounded-lg animate-pulse mx-auto" />
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                    {[...Array(12)].map((_, i) => (
                        <ProductCardSkeleton key={i} />
                    ))}
                </div>
            </div>
        )
    }
    if (isError) return <Error message={`${translate("shop.error")} ${error?.message}`} />

    const products = productsData?.data || []
    const meta = productsData?.meta || { current_page: 1, last_page: 1 }

    return (
        <div className="w-full flex flex-col md:flex-row gap-8">
            {/* Filters Sidebar */}
            <div className="w-full md:w-64 flex-shrink-0 space-y-6">
                <div>
                    <h3 className="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <Filter size={20} />
                        Bộ lọc
                    </h3>
                    <form onSubmit={handleSearch} className="relative">
                        <FormInput placeholder="Tìm kiếm..." value={params.search} onChange={(e) => setParams((prev) => ({ ...prev, search: e.target.value }))} />
                        <button type="submit" className="absolute right-3 top-3 text-slate-400">
                            <Search size={18} />
                        </button>
                    </form>
                </div>

                <div>
                    <h4 className="font-medium text-sm text-slate-800 mb-3">Sắp xếp</h4>
                    <select className="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors" value={params.sort} onChange={(e) => setParams((prev) => ({ ...prev, sort: e.target.value, page: 1 }))}>
                        <option value="newest">Mới nhất</option>
                        <option value="price_asc">Giá tăng dần</option>
                        <option value="price_desc">Giá giảm dần</option>
                    </select>
                </div>
            </div>

            {/* Product List */}
            <div className="flex-1">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-slate-800 tracking-tight mb-2">{translate("shop.title")}</h1>
                    <p className="text-slate-500">{translate("shop.subtitle")}</p>
                </div>

                {isLoading ? (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                        {[...Array(12)].map((_, i) => (
                            <ProductCardSkeleton key={i} />
                        ))}
                    </div>
                ) : products.length === 0 ? (
                    <div className="w-full py-20 flex flex-col items-center justify-center text-center bg-white/50 rounded-2xl border border-slate-100">
                        <div className="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6">
                            <PackageOpen size={40} />
                        </div>
                        <h2 className="text-2xl font-bold text-slate-800 mb-2">{translate("shop.empty")}</h2>
                        <button onClick={() => setParams({ page: 1, per_page: 12, search: "", category_id: "", sort: "newest" })} className="text-blue-600 font-medium hover:underline mt-2">
                            Xóa bộ lọc
                        </button>
                    </div>
                ) : (
                    <>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                            {products.map((product) => (
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>

                        <Pagination currentPage={meta.current_page} totalPages={meta.last_page} onPageChange={handlePageChange} />
                    </>
                )}
            </div>
        </div>
    )
}

export default ShopPage
