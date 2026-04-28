import React from "react"
import { useNavigate } from "react-router-dom"
import { ArrowLeft } from "lucide-react"
import useProductDetail from "../features/product/hooks/useProductDetail"
import ProductImage from "../features/product/components/ProductImage"
import ProductInfo from "../features/product/components/ProductInfo"
import Loading from "../components/common/Loading"
import Error from "../components/common/Error"
import useSettingsStore from "../store/useSettingsStore"

const ProductDetailPage = () => {
    const navigate = useNavigate()
    const translate = useSettingsStore((state) => state.translate)
    const { product, isLoading, isError, error, quantity, handleAddToCart, incrementQuantity, decrementQuantity } = useProductDetail()

    if (isLoading) return <Loading />
    if (isError || !product) return <Error message={error?.message || translate("product.not_found")} />

    return (
        <div className="w-full max-w-5xl mx-auto bg-white/60 backdrop-blur-xl rounded-3xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10">
            <button onClick={() => navigate(-1)} className="flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors mb-8">
                <ArrowLeft size={20} />
                <span className="font-medium">{translate("common.back")}</span>
            </button>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16">
                <ProductImage src={product.image?.url} alt={product.name} />

                <ProductInfo product={product} quantity={quantity} onIncrement={incrementQuantity} onDecrement={decrementQuantity} onAddToCart={handleAddToCart} translate={translate} />
            </div>
        </div>
    )
}

export default ProductDetailPage
