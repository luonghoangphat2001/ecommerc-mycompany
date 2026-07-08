import React from "react"
import { useNavigate, useParams } from "react-router-dom"
import { ArrowLeft, TrendingUp, ShoppingBag } from "lucide-react"
import { useQuery } from '@tanstack/react-query'
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
    
    // Fetch product by slug
    const { data: productData, isLoading, isError, error } = useQuery({
        queryKey: ['product-by-slug', slug],
        queryFn: () => productApi.getProductBySlug(slug),
        enabled: !!slug,
    });
    
    const product = productData?.data
    
    // Use product detail hook with product object
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
        setSelectedWarehouse 
    } = useProductDetail(product)
    
    if (isLoading || !product) return <Loading />
    if (isError || !product) return <Error message={error?.message || translate("product.not_found")} />

    return (
        <div className="w-full max-w-5xl mx-auto bg-white/60 backdrop-blur-xl rounded-3xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10">
            <button onClick={() => navigate(-1)} className="flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors mb-8">
                <ArrowLeft size={20} />
                <span className="font-medium">{translate("common.back")}</span>
            </button>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16">
                <ProductImage src={product.image?.url} alt={product.name} />

                <div>
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

            <RelatedProducts 
                products={upsells} 
                title="Sản phẩm mua kèm (Upsell)" 
                icon={TrendingUp} 
            />
            
            <RelatedProducts 
                products={crossSells} 
                title="Sản phẩm mua thêm (Cross-sell)" 
                icon={ShoppingBag} 
            />
        </div>
    )
}

export default ProductDetailPage
