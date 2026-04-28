import React from "react"
import { Link } from "react-router-dom"
import { ShoppingBag } from "lucide-react"
import useCartStore from "../features/cart/store/useCartStore"
import useSettingsStore from "../store/useSettingsStore"
import CartItem from "../features/cart/components/CartItem"
import CartSummary from "../features/cart/components/CartSummary"
import { EmptyState, Button, PageHeading } from "../components/common"

const CartPage = () => {
    const { items, updateQuantity, removeFromCart, getCartTotal } = useCartStore()
    const translate = useSettingsStore((state) => state.translate)

    if (items.length === 0) {
        return (
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
        )
    }

    return (
        <div className="w-full max-w-6xl mx-auto">
            <PageHeading className="mb-10">{translate("cart.page_title")}</PageHeading>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div className="lg:col-span-2 space-y-4">
                    {items.map((item) => (
                        <CartItem key={item.id} item={item} onUpdateQuantity={updateQuantity} onRemove={removeFromCart} />
                    ))}
                </div>

                <div className="lg:col-span-1">
                    <CartSummary total={getCartTotal()} translate={translate} />
                </div>
            </div>
        </div>
    )
}

export default CartPage
