import React, { useState, useEffect } from "react"
import { useSearchParams } from "react-router-dom"
import { PackageOpen, SlidersHorizontal, Search, RotateCcw, X } from "lucide-react"
import { useProducts, useProductCategories, useBrands } from "../features/product/hooks/useProducts"
import ProductCard from "../features/product/components/ProductCard"
import Error from "../components/common/Error"
import { ProductCardSkeleton } from "../components/common/Skeleton"
import useSettingsStore from "../store/useSettingsStore"
import Pagination from "../components/common/Pagination"
import FormInput from "../components/common/FormInput"
import Button from "../components/common/Button"
import { getLocalizedText } from "../utils/formatters"
import useSeo from "../utils/useSeo"

const ShopPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const [searchParams, setSearchParams] = useSearchParams()
    const urlSearch = searchParams.get("search") || ""
    const urlCategory = searchParams.get("category_id") || ""
    const urlBrand = searchParams.get("brand_id") || ""

    useSeo({
        title: translate("shop.title") || "Product Catalog",
        description: translate("shop.subtitle") || "Browse our complete collection of verified merchandise.",
        canonicalPath: "/shop",
    })

    const [params, setParams] = useState({
        page: 1,
        per_page: 12,
        search: urlSearch,
        category_id: urlCategory,
        brand_id: urlBrand,
        sort: "newest",
    })

    useEffect(() => {
        if (urlSearch !== params.search) {
            setParams((prev) => ({ ...prev, search: urlSearch, page: 1 }))
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [urlSearch])

    const { data: productsData, isLoading, isError, error } = useProducts(params)
    const { data: categories = [] } = useProductCategories()
    const { data: brands = [] } = useBrands()

    const handlePageChange = (page) => {
        setParams((prev) => ({ ...prev, page }))
        window.scrollTo({ top: 0, behavior: "smooth" })
    }

    const handleSearch = (e) => {
        e.preventDefault()
        setParams((prev) => ({ ...prev, page: 1 }))
    }

    const handleResetFilters = () => {
        setSearchParams({})
        setParams({
            page: 1,
            per_page: 12,
            search: "",
            category_id: "",
            brand_id: "",
            sort: "newest",
        })
    }

    if (isError) return <Error message={`${translate("shop.error")} ${error?.message}`} />

    const products = productsData?.data || []
    const meta = productsData?.meta || { current_page: 1, last_page: 1, total: products.length }
    const hasActiveFilters = Boolean(
        params.search || params.category_id || params.brand_id || params.sort !== "newest"
    )

    return (
        <div className="w-full">
            {/* Page Header */}
            <div className="mb-6 pb-5 border-b border-slate-200/90 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <h1 className="text-2xl sm:text-3xl font-bold text-[#09253E] tracking-tight">
                        {translate("shop.title") || "Product Catalog"}
                    </h1>
                    <p className="text-sm text-slate-500 mt-1">
                        {translate("shop.subtitle") || "Browse our complete collection of verified merchandise."}
                    </p>
                </div>

                <div className="flex items-center gap-3 text-xs text-slate-500 font-medium tabular-nums">
                    {!isLoading && (
                        <span className="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-slate-700 font-semibold">
                            {translate("shop.items_available", { count: meta.total ?? products.length }) ||
                                `${meta.total ?? products.length} items available`}
                        </span>
                    )}
                </div>
            </div>

            <div className="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
                {/* Filter Sidebar Card */}
                <aside className="w-full lg:w-64 flex-shrink-0 bg-white border border-slate-200/90 rounded-xl p-5 shadow-card space-y-5">
                    <div className="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <h2 className="text-sm font-bold text-[#09253E] flex items-center gap-2">
                            <SlidersHorizontal size={16} className="text-[#004687]" />
                            <span>{translate("shop.filter_and_sort") || "Filter & Sort"}</span>
                        </h2>
                        {hasActiveFilters && (
                            <button
                                type="button"
                                onClick={handleResetFilters}
                                className="inline-flex items-center gap-1 text-xs font-semibold text-[#004687] hover:underline"
                            >
                                <RotateCcw size={12} />
                                <span>{translate("shop.reset") || "Reset"}</span>
                            </button>
                        )}
                    </div>

                    {/* Keyword Search */}
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            {translate("shop.keyword") || "Keyword"}
                        </label>
                        <form onSubmit={handleSearch}>
                            <FormInput
                                name="catalog-search"
                                icon={Search}
                                placeholder={translate("shop.search_placeholder") || "Search products..."}
                                value={params.search}
                                onChange={(e) => setParams((prev) => ({ ...prev, search: e.target.value, page: 1 }))}
                            />
                        </form>
                    </div>

                    {/* Category Filter */}
                    <div>
                        <label
                            htmlFor="catalog-category"
                            className="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2"
                        >
                            {translate("shop.category") || "Category"}
                        </label>
                        <select
                            id="catalog-category"
                            className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687] transition-colors"
                            value={params.category_id}
                            onChange={(e) => setParams((prev) => ({ ...prev, category_id: e.target.value, page: 1 }))}
                        >
                            <option value="">{translate("shop.all_categories") || "All Categories"}</option>
                            {categories.map((cat) => (
                                <option key={cat.id} value={cat.id}>
                                    {getLocalizedText(cat.name)}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Brand Filter */}
                    <div>
                        <label
                            htmlFor="catalog-brand"
                            className="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2"
                        >
                            {translate("shop.brand") || "Brand"}
                        </label>
                        <select
                            id="catalog-brand"
                            className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687] transition-colors"
                            value={params.brand_id}
                            onChange={(e) => setParams((prev) => ({ ...prev, brand_id: e.target.value, page: 1 }))}
                        >
                            <option value="">{translate("shop.all_brands") || "All Brands"}</option>
                            {brands.map((brand) => (
                                <option key={brand.id} value={brand.id}>
                                    {getLocalizedText(brand.name)}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Sort Order */}
                    <div>
                        <label
                            htmlFor="catalog-sort"
                            className="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2"
                        >
                            {translate("shop.sort_by") || "Sort By"}
                        </label>
                        <select
                            id="catalog-sort"
                            className="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687] transition-colors"
                            value={params.sort}
                            onChange={(e) => setParams((prev) => ({ ...prev, sort: e.target.value, page: 1 }))}
                        >
                            <option value="newest">{translate("shop.sort_newest") || "Newest Arrivals"}</option>
                            <option value="price_asc">{translate("shop.sort_price_asc") || "Price: Low to High"}</option>
                            <option value="price_desc">{translate("shop.sort_price_desc") || "Price: High to Low"}</option>
                        </select>
                    </div>
                </aside>

                {/* Main Product Grid Area */}
                <div className="flex-1 w-full min-w-0">
                    {/* Active Filter Chips */}
                    {(params.search || params.category_id || params.brand_id) && (
                        <div className="mb-4 flex items-center gap-2 flex-wrap">
                            <span className="text-xs text-slate-500">
                                {translate("shop.active_filter") || "Active filter:"}
                            </span>
                            {params.search && (
                                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#004687]/10 text-[#004687] text-xs font-semibold">
                                    <span>"{params.search}"</span>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSearchParams({})
                                            setParams((prev) => ({ ...prev, search: "", page: 1 }))
                                        }}
                                        className="hover:text-[#09253E]"
                                    >
                                        <X size={13} />
                                    </button>
                                </span>
                            )}
                            {params.category_id && (
                                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#004687]/10 text-[#004687] text-xs font-semibold">
                                    <span>
                                        {getLocalizedText(
                                            categories.find((c) => String(c.id) === String(params.category_id))?.name
                                        ) || params.category_id}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => setParams((prev) => ({ ...prev, category_id: "", page: 1 }))}
                                        className="hover:text-[#09253E]"
                                    >
                                        <X size={13} />
                                    </button>
                                </span>
                            )}
                            {params.brand_id && (
                                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#004687]/10 text-[#004687] text-xs font-semibold">
                                    <span>
                                        {getLocalizedText(
                                            brands.find((b) => String(b.id) === String(params.brand_id))?.name
                                        ) || params.brand_id}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => setParams((prev) => ({ ...prev, brand_id: "", page: 1 }))}
                                        className="hover:text-[#09253E]"
                                    >
                                        <X size={13} />
                                    </button>
                                </span>
                            )}
                        </div>
                    )}

                    {isLoading ? (
                        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            {[...Array(9)].map((_, i) => (
                                <ProductCardSkeleton key={i} />
                            ))}
                        </div>
                    ) : products.length === 0 ? (
                        <div className="w-full py-16 px-4 flex flex-col items-center justify-center text-center bg-white rounded-xl border border-slate-200/90 shadow-card">
                            <div className="w-14 h-14 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 mb-4">
                                <PackageOpen size={28} />
                            </div>
                            <h2 className="text-lg font-bold text-[#09253E] mb-1">{translate("shop.empty")}</h2>
                            <p className="text-sm text-slate-500 mb-5 max-w-sm">
                                {translate("shop.empty_subtitle") ||
                                    "No products matched your current filter criteria. Try adjusting your search keyword."}
                            </p>
                            <Button variant="outline" size="sm" onClick={handleResetFilters}>
                                {translate("shop.clear_filters") || "Clear All Filters"}
                            </Button>
                        </div>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                                {products.map((product) => (
                                    <ProductCard key={product.id} product={product} />
                                ))}
                            </div>

                            <Pagination
                                currentPage={meta.current_page}
                                totalPages={meta.last_page}
                                onPageChange={handlePageChange}
                            />
                        </>
                    )}
                </div>
            </div>
        </div>
    )
}

export default ShopPage
