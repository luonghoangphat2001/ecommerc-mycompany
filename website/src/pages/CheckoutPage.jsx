import React from "react"
import useCartStore from "../features/cart/store/useCartStore"
import useAuthStore from "../features/auth/store/useAuthStore"
import useSettingsStore from "../store/useSettingsStore"
import useCheckoutForm from "../features/checkout/hooks/useCheckoutForm"
import CheckoutForm from "../features/checkout/components/CheckoutForm"
import OrderSummary from "../features/checkout/components/OrderSummary"
import { PageHeading } from "../components/common"

const CheckoutPage = () => {
    const { items, summary, clearCart } = useCartStore()
    const { user } = useAuthStore()
    const translate = useSettingsStore((state) => state.translate)

    const {
        formData,
        isSubmitting,
        handleSubmit,
        handleInputChange,
        handlePaymentChange,
        handleShippingChange,
        handleBillingToggle,
    } = useCheckoutForm(user, clearCart)

    const summaryData = summary || {}

    return (
        <div className="w-full max-w-6xl mx-auto">
            <div className="mb-6 pb-4 border-b border-slate-200/90">
                <PageHeading size="lg">{translate("checkout.shipping_info")}</PageHeading>
                <p className="text-sm text-slate-500 mt-1">
                    Complete your delivery and payment details below to finalize your order.
                </p>
            </div>

            <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <CheckoutForm
                    formData={formData}
                    onInputChange={handleInputChange}
                    onPaymentChange={handlePaymentChange}
                    onShippingChange={handleShippingChange}
                    onBillingToggle={handleBillingToggle}
                    isSubmitting={isSubmitting}
                />

                <OrderSummary items={items} summary={summaryData} isSubmitting={isSubmitting} />
            </form>
        </div>
    )
}

export default CheckoutPage
