import React from "react"
import { ShoppingCart } from "lucide-react"
import { Button } from "../../../components/common"
import { useFormatters } from "../../../utils/useFormatters"

const ProductInfo = ({ product, stock, quantity, onIncrement, onDecrement, onAddToCart, translate }) => {
    const { formatCurrency } = useFormatters()

    return (
        <div className="flex flex-col justify-center">
            {product.is_featured && <span className="inline-block px-3 py-1 text-xs font-bold text-blue-600 bg-blue-50 rounded-full mb-4 w-max">{translate("product.featured")}</span>}

            <h1 className="text-3xl md:text-4xl font-bold text-slate-900 mb-4 tracking-tight">{product.name}</h1>

            <div className="text-3xl font-black text-blue-600 mb-6">{formatCurrency(product.price)}</div>

            <div className="prose prose-slate text-slate-600 mb-8 leading-relaxed">
                <p>{product.description || translate("product.no_description")}</p>
            </div>

            <div className="flex flex-col gap-3 mb-8 border-y border-slate-100 py-6">
                {product.brand && (
                    <div className="flex items-center gap-2">
                        <span className="text-slate-500 font-medium text-sm w-24">{translate("product.brand")}:</span>
                        <span className="text-slate-900 font-bold">{product.brand.name}</span>
                    </div>
                )}
                {product.categories && product.categories.length > 0 && (
                    <div className="flex items-start gap-2">
                        <span className="text-slate-500 font-medium text-sm w-24">{translate("product.categories")}:</span>
                        <div className="flex flex-wrap gap-2">
                            {product.categories.map((cat) => (
                                <span key={cat.id} className="text-xs font-semibold bg-slate-100 text-slate-600 px-2 py-1 rounded-md">
                                    {cat.name}
                                </span>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            <div className="flex items-center gap-4 mb-8">
                <div className="flex items-center bg-slate-100 rounded-full px-4 py-2 border border-slate-200">
                    <button onClick={onDecrement} className="text-slate-500 hover:text-blue-600 font-bold px-2">
                        -
                    </button>
                    <span className="w-10 text-center font-semibold text-slate-800">{quantity}</span>
                    <button onClick={onIncrement} className="text-slate-500 hover:text-blue-600 font-bold px-2">
                        +
                    </button>
                </div>
                <div className={`text-sm font-medium ${stock > 0 ? 'text-green-600' : 'text-red-500'}`}>
                    {stock > 0 ? 'Còn hàng' : 'Hết hàng'}
                </div>
            </div>

            <Button 
                onClick={() => { 
                  console.log('Button clicked - stock:', stock, 'product available:', stock > 0);
                  onAddToCart(); 
                }} 
                disabled={stock <= 0}
                variant="blue" 
                size="block" 
                className="gap-3 !rounded-full hover:-translate-y-0.5 hover:shadow-xl"
            >
                <ShoppingCart size={22} />
                {stock > 0 ? translate("product.add_to_cart") : 'Hết hàng'} ({stock || 0})
            </Button>
        </div>
    )
}

export default ProductInfo
