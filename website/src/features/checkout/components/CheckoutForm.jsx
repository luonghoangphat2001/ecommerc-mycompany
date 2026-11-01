import React from "react"
import { MapPin, CreditCard, Truck, Package } from "lucide-react"
import useSettingsStore from "../../../store/useSettingsStore"
import useCheckoutAddress from "../hooks/useCheckoutAddress"
import { useFormatters } from "../../../utils/useFormatters"
import { paymentStrategiesMap } from "./PaymentStrategies"
import { FormInput, FormSelect, FormCheckbox, FormRadio, FormSection } from "../../../components/common"

const CheckoutForm = ({ formData, onInputChange, onPaymentChange, onShippingChange, onBillingToggle }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency, getLocalizedText } = useFormatters()
    const { countries, states, regions, subRegions, paymentMethods, shippingMethods } =
        useCheckoutAddress(formData)

    const isVietnam = formData.country?.toUpperCase() === "VN"
    const isBillingVietnam = formData.billingCountry?.toUpperCase() === "VN"

    return (
        <div className="lg:col-span-2 space-y-6">
            <FormSection
                title={translate("checkout.shipping_info") || "Shipping Information"}
                icon={MapPin}
                iconColor="blue"
            >
                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <FormInput
                        label={translate("checkout.first_name") || "First Name"}
                        name="firstName"
                        required
                        value={formData.firstName}
                        onChange={onInputChange}
                    />
                    <FormInput
                        label={translate("checkout.last_name") || "Last Name"}
                        name="lastName"
                        required
                        value={formData.lastName}
                        onChange={onInputChange}
                    />
                    <FormInput
                        label={translate("checkout.email") || "Email"}
                        name="email"
                        type="email"
                        required
                        value={formData.email}
                        onChange={onInputChange}
                    />
                    <FormInput
                        label={translate("checkout.phone") || "Phone"}
                        name="phone"
                        type="tel"
                        required
                        value={formData.phone}
                        onChange={onInputChange}
                    />
                    <FormSelect
                        label={translate("checkout.country") || "Country"}
                        name="country"
                        required
                        value={formData.country}
                        onChange={onInputChange}
                        options={Object.entries(countries)}
                        placeholder={translate("checkout.select_country") || "Select country"}
                    />
                    {!isVietnam && (
                        <FormInput
                            label={translate("checkout.city") || "City"}
                            name="city"
                            required
                            value={formData.city}
                            onChange={onInputChange}
                        />
                    )}

                    {isVietnam && (
                        <>
                            <FormSelect
                                label={translate("checkout.state") || "Province / City"}
                                name="state"
                                required
                                value={formData.state}
                                onChange={onInputChange}
                                options={Object.entries(states)}
                                placeholder={translate("checkout.select_state") || "Select province/city"}
                            />
                            {formData.state && (
                                <FormSelect
                                    label={translate("checkout.region") || "District"}
                                    name="region"
                                    value={formData.region}
                                    onChange={onInputChange}
                                    options={Object.entries(regions)}
                                    placeholder={translate("checkout.select_region") || "Select district"}
                                />
                            )}
                            {formData.region && (
                                <FormSelect
                                    label={translate("checkout.sub_region") || "Ward / Commune"}
                                    name="subRegion"
                                    value={formData.subRegion}
                                    onChange={onInputChange}
                                    options={Object.entries(subRegions)}
                                    placeholder={translate("checkout.select_sub_region") || "Select ward"}
                                />
                            )}
                        </>
                    )}
                    <FormInput
                        label={translate("checkout.address") || "Street Address"}
                        name="address"
                        required
                        value={formData.address}
                        onChange={onInputChange}
                        className="md:col-span-2"
                    />

                    <FormInput
                        label={translate("checkout.note") || "Delivery Instructions"}
                        name="note"
                        value={formData.note}
                        onChange={onInputChange}
                        className="md:col-span-2"
                    />
                </div>
            </FormSection>

            <FormSection
                title={translate("checkout.billing_info") || "Billing Address"}
                icon={MapPin}
                iconColor="slate"
            >
                <FormCheckbox
                    name="billingSameAsShipping"
                    label={
                        translate("checkout.billing_same_as_shipping") ||
                        "Billing address is the same as shipping address"
                    }
                    checked={formData.billingSameAsShipping}
                    onChange={onBillingToggle}
                />

                {!formData.billingSameAsShipping && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5 pt-5 border-t border-slate-100">
                        <FormInput
                            label={translate("checkout.first_name") || "First Name"}
                            name="billingFirstName"
                            required
                            value={formData.billingFirstName}
                            onChange={onInputChange}
                        />
                        <FormInput
                            label={translate("checkout.last_name") || "Last Name"}
                            name="billingLastName"
                            required
                            value={formData.billingLastName}
                            onChange={onInputChange}
                        />
                        <FormInput
                            label={translate("checkout.email") || "Email"}
                            name="billingEmail"
                            type="email"
                            required
                            value={formData.billingEmail}
                            onChange={onInputChange}
                        />
                        <FormInput
                            label={translate("checkout.phone") || "Phone"}
                            name="billingPhone"
                            type="tel"
                            required
                            value={formData.billingPhone}
                            onChange={onInputChange}
                        />
                        <FormSelect
                            label={translate("checkout.country") || "Country"}
                            name="billingCountry"
                            required
                            value={formData.billingCountry}
                            onChange={onInputChange}
                            options={Object.entries(countries)}
                            placeholder={translate("checkout.select_country") || "Select country"}
                        />
                        {!isBillingVietnam && (
                            <FormInput
                                label={translate("checkout.city") || "City"}
                                name="billingCity"
                                required
                                value={formData.billingCity}
                                onChange={onInputChange}
                            />
                        )}

                        {isBillingVietnam && (
                            <>
                                <FormSelect
                                    label={translate("checkout.state") || "Province / City"}
                                    name="billingState"
                                    required
                                    value={formData.billingState}
                                    onChange={onInputChange}
                                    options={Object.entries(states)}
                                    placeholder={translate("checkout.select_state") || "Select province/city"}
                                />
                                {formData.billingState && (
                                    <FormSelect
                                        label={translate("checkout.region") || "District"}
                                        name="billingRegion"
                                        value={formData.billingRegion}
                                        onChange={onInputChange}
                                        options={Object.entries(regions)}
                                        placeholder={translate("checkout.select_region") || "Select district"}
                                    />
                                )}
                                {formData.billingRegion && (
                                    <FormSelect
                                        label={translate("checkout.sub_region") || "Ward / Commune"}
                                        name="billingSubRegion"
                                        value={formData.billingSubRegion}
                                        onChange={onInputChange}
                                        options={Object.entries(subRegions)}
                                        placeholder={translate("checkout.select_sub_region") || "Select ward"}
                                    />
                                )}
                            </>
                        )}
                        <FormInput
                            label={translate("checkout.address") || "Street Address"}
                            name="billingAddress"
                            required
                            value={formData.billingAddress}
                            onChange={onInputChange}
                            className="md:col-span-2"
                        />
                    </div>
                )}
            </FormSection>

            <FormSection
                title={translate("checkout.payment_method") || "Payment Method"}
                icon={CreditCard}
                iconColor="blue"
            >
                <div className="space-y-3">
                    {paymentMethods.map((method) => {
                        const methodId = method.id || method.code
                        const localizedMethodName =
                            translate(`checkout.${methodId}`) || getLocalizedText(method.name) || methodId
                        return (
                            <FormRadio
                                key={methodId}
                                name="paymentMethod"
                                value={methodId}
                                label={localizedMethodName}
                                checked={String(formData.paymentMethod) === String(methodId)}
                                onChange={onPaymentChange}
                                accessory={
                                    method.icon === "truck" || methodId === "cod" ? (
                                        <Truck size={18} className="text-[#004687]" />
                                    ) : method.icon === "credit-card" || methodId === "bank_transfer" ? (
                                        <CreditCard size={18} className="text-[#004687]" />
                                    ) : null
                                }
                            />
                        )
                    })}
                </div>
                {(() => {
                    const SelectedStrategy = paymentStrategiesMap[formData.paymentMethod]
                    return SelectedStrategy ? <SelectedStrategy /> : null
                })()}
            </FormSection>

            {shippingMethods.length > 0 && (
                <FormSection
                    title={translate("checkout.shipping_method") || "Delivery Method"}
                    icon={Package}
                    iconColor="green"
                >
                    <div className="space-y-3">
                        {shippingMethods.map((method, index) => {
                            const methodId = method.id ?? method.method_id ?? method.code ?? method.type ?? `method-${index}`
                            const methodValue = method.code || method.type || String(methodId)
                            const cost = method.cost ?? method.amount ?? method.settings?.cost ?? 0
                            const isChecked =
                                String(formData.shippingMethod) === String(methodValue) ||
                                String(formData.shippingMethod) === String(methodId)
                            return (
                                <FormRadio
                                    key={methodId}
                                    name="shippingMethod"
                                    value={methodValue}
                                    label={getLocalizedText(method.name || method.title) || methodValue}
                                    checked={isChecked}
                                    onChange={onShippingChange}
                                    accentColor="green"
                                >
                                    {cost > 0 ? (
                                        <p className="text-xs text-emerald-700 font-semibold tabular-nums mt-0.5">
                                            {formatCurrency(cost)}
                                        </p>
                                    ) : (
                                        <p className="text-xs text-emerald-700 font-semibold mt-0.5">
                                            {translate("cart.free") || "Free"}
                                        </p>
                                    )}
                                </FormRadio>
                            )
                        })}
                    </div>
                </FormSection>
            )}
        </div>
    )
}

export default CheckoutForm
