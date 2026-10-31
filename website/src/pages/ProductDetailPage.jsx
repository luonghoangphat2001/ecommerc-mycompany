import React from "react"
import { Link, useNavigate, useParams } from "react-router-dom"
import { ArrowLeft, TrendingUp, ShoppingBag, ChevronRight } from "lucide-react"
import { useQuery } from "@tanstack/react-query"
import productApi from "../features/product/services/productApi"
import useProductDetail from "../features/product/hooks/useProductDetail"
import ProductImage from "../features/product/components/ProductImage"
import ProductInfo from "../features/product/components/ProductInfo"
import ProductInventoryDetail from "../features/product/components/ProductInventoryDetail"
import RelatedProducts from "../features/product/components/RelatedProducts"
import Loading from "../components/common/Loading"
import Error from "../components/common/Error"
import useSettingsStore from "../store/useSettingsStore"

const ProductDetailPage = () => {
    const { slug } = useParams()
    const navigate = useNavigate()
    const translate = useSettingsStore((state) => state.translate)

    const { data: productData, isLoading, isError, error } = useQuery({
        queryKey: ["product-by-slug", slug],
        queryFn: () => productApi.getProductBySlug(slug),
        enabled: !!slug,
    })

    const product = productData?.data

    const {
        stock,
        inventory,
        upsells,
        crossSells,
        quantity,
        handleAddToCart,
        incrementQuantity,
        decrementQuantity,
        selectedWarehouse,
        setSelectedWarehouse,
    } = useProductDetail(product)

    if (isLoading || !product) return <Loading />
    if (isError || !product) return <Error message={error?.message || translate("product.not_found")} />

    return (
        <div className="w-full max-w-6xl mx-auto">
            {/* Breadcrumb & Back Bar */}
            <div className="flex items-center justify-between gap-4 mb-5">
                <nav aria-label="Breadcrumb" className="flex items-center gap-1.5 text-xs text-slate-500 min-w-0">
                    <Link to="/" className="hover:text-[#004687] transition-colors shrink-0">
                        {translate("header.home") || "Home"}
                    </Link>
                    <ChevronRight size={13} className="text-slate-400 shrink-0" />
                    <Link to="/shop" className="hover:text-[#004687] transition-colors shrink-0">
                        {translate("header.shop") || "Catalog"}
                    </Link>
                    <ChevronRight size={13} className="text-slate-400 shrink-0" />
                    <span className="text-slate-800 font-semibold truncate">{product.name}</span>
                </nav>

                <button
                    type="button"
                    onClick={() => navigate(-1)}
                    className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-[#004687] transition-colors shrink-0"
                >
                    <ArrowLeft size={15} />
                    <span>{translate("common.back")}</span>
                </button>
            </div>

            {/* Product Detail Card */}
            <div className="bg-white rounded-xl border border-slate-200/90 shadow-card p-6 sm:p-8 lg:p-10">
                <div className="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">
                    <div className="md:col-span-6">
                        <ProductImage src={product.image?.url || product.image} alt={product.name} />
                    </div>

                    <div className="md:col-span-6">
                        <ProductInfo
                            product={product}
                            stock={stock}
                            quantity={quantity}
                            onIncrement={incrementQuantity}
                            onDecrement={decrementQuantity}
                            onAddToCart={handleAddToCart}
                            translate={translate}
                        />
                        <ProductInventoryDetail
                            inventory={inventory}
                            selectedWarehouse={selectedWarehouse}
                            onSelectWarehouse={setSelectedWarehouse}
                        />
                    </div>
                </div>

                <RelatedProducts products={upsells} title="Recommended Upgrades" icon={TrendingUp} />

                <RelatedProducts products={crossSells} title="Frequently Bought Together" icon={ShoppingBag} />
            </div>
        </div>
    )
}

export default ProductDetailPage
