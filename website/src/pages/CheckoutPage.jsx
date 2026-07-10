import React from "react"
import useCartStore from "../features/cart/store/useCartStore"
import useAuthStore from "../features/auth/store/useAuthStore"
import useSettingsStore from "../store/useSettingsStore"
import useCheckoutForm from "../features/checkout/hooks/useCheckoutForm"
import CheckoutForm from "../features/checkout/components/CheckoutForm"
import OrderSummary from "../features/checkout/components/OrderSummary"

const CheckoutPage = () => {
    const { items, summary, clearCart } = useCartStore()
    const { user } = useAuthStore()
    const translate = useSettingsStore((state) => state.translate)

    const { formData, isSubmitting, handleSubmit, handleInputChange, handlePaymentChange, handleShippingChange, handleBillingToggle } = useCheckoutForm(user, clearCart)

    const shippingCost = summary?.shipping?.amount || 0
    const cartTotal = summary?.total || 0

    return (
        <div className="w-full max-w-6xl mx-auto">
            <h1 className="text-3xl font-black text-slate-900 mb-10 tracking-tight">{translate("checkout.shipping_info")}</h1>

            <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <CheckoutForm formData={formData} onInputChange={handleInputChange} onPaymentChange={handlePaymentChange} onShippingChange={handleShippingChange} onBillingToggle={handleBillingToggle} isSubmitting={isSubmitting} />

                <OrderSummary items={items} total={cartTotal} shippingCost={shippingCost} isSubmitting={isSubmitting} />
            </form>
        </div>
    )
}

export default CheckoutPage
