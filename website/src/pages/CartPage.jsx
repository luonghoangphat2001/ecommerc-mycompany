import React from "react"
import { Link } from "react-router-dom"
import { ShoppingBag } from "lucide-react"
import useCartStore from "../features/cart/store/useCartStore"
import useSettingsStore from "../store/useSettingsStore"
import CartItem from "../features/cart/components/CartItem"
import CartSummary from "../features/cart/components/CartSummary"
import { EmptyState, Button, PageHeading } from "../components/common"
import useSeo from "../utils/useSeo"

const CartPage = () => {
    const { items, summary, updateQuantity, removeFromCart, getCartTotal } = useCartStore()
    const translate = useSettingsStore((state) => state.translate)

    useSeo({
        title: translate("cart.page_title") || "Shopping Cart",
        canonicalPath: "/cart",
    })

    if (items.length === 0) {
        return (
            <div className="bg-white rounded-xl border border-slate-200/90 shadow-card">
                <EmptyState
                    icon={ShoppingBag}
                    title={translate("cart.empty_title")}
                    description={translate("cart.empty_subtitle")}
                    action={
                        <Button as={Link} to="/shop" size="lg">
                            {translate("cart.continue_shopping")}
                        </Button>
                    }
                />
            </div>
        )
    }

    return (
        <div className="w-full max-w-6xl mx-auto">
            <div className="mb-6 pb-4 border-b border-slate-200/90 flex items-baseline justify-between">
                <PageHeading size="lg">{translate("cart.page_title")}</PageHeading>
                <span className="text-xs font-semibold text-slate-500 tabular-nums">
                    {items.length} {translate("cart.items")}
                </span>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2 space-y-3.5">
                    {items.map((item) => (
                        <CartItem
                            key={item.id}
                            item={item}
                            onUpdateQuantity={updateQuantity}
                            onRemove={removeFromCart}
                        />
                    ))}
                </div>

                <div className="lg:col-span-1">
                    <CartSummary summary={summary} total={getCartTotal()} translate={translate} />
                </div>
            </div>
        </div>
    )
}

export default CartPage
