import React from "react"
import { MapPin, CreditCard, Truck, Package } from "lucide-react"
import useSettingsStore from "../../../store/useSettingsStore"
import useCheckoutAddress from "../hooks/useCheckoutAddress"
import { useFormatters } from "../../../utils/useFormatters"
import { FormInput, FormSelect, FormCheckbox, FormRadio, FormSection } from "../../../components/common"

const CheckoutForm = ({ formData, onInputChange, onPaymentChange, onShippingChange, onBillingToggle }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()
    const { countries, states, regions, subRegions, paymentMethods, shippingMethods } = useCheckoutAddress(formData)

    return (
        <div className="lg:col-span-2 space-y-8">
            <FormSection title={translate("checkout.shipping_info") || "Thông tin giao hàng"} icon={MapPin} iconColor="blue">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormInput label={translate("checkout.first_name") || "Họ"} name="firstName" required value={formData.firstName} onChange={onInputChange} />
                    <FormInput label={translate("checkout.last_name") || "Tên"} name="lastName" required value={formData.lastName} onChange={onInputChange} />
                    <FormInput label={translate("checkout.email") || "Email"} name="email" type="email" required value={formData.email} onChange={onInputChange} />
                    <FormInput label={translate("checkout.phone") || "Số điện thoại"} name="phone" type="tel" required value={formData.phone} onChange={onInputChange} />
                    <FormInput label={translate("checkout.address") || "Địa chỉ"} name="address" required value={formData.address} onChange={onInputChange} className="md:col-span-2" />
                    <FormInput label={translate("checkout.city") || "Thành phố"} name="city" required value={formData.city} onChange={onInputChange} />
                    <FormSelect label={translate("checkout.country") || "Quốc gia"} name="country" required value={formData.country} onChange={onInputChange} options={Object.entries(countries)} placeholder={translate("checkout.select_country") || "Chọn quốc gia"} />

                    {formData.country === "vn" && (
                        <>
                            <FormSelect label={translate("checkout.state") || "Tỉnh/Thành phố"} name="state" required value={formData.state} onChange={onInputChange} options={Object.entries(states)} placeholder={translate("checkout.select_state") || "Chọn tỉnh/thành phố"} />
                            {formData.state && <FormSelect label={translate("checkout.region") || "Quận/Huyện"} name="region" value={formData.region} onChange={onInputChange} options={Object.entries(regions)} placeholder={translate("checkout.select_region") || "Chọn quận/huyện"} />}
                            {formData.region && <FormSelect label={translate("checkout.sub_region") || "Phường/Xã"} name="subRegion" value={formData.subRegion} onChange={onInputChange} options={Object.entries(subRegions)} placeholder={translate("checkout.select_sub_region") || "Chọn phường/xã"} />}
                        </>
                    )}

                    <FormInput label={translate("checkout.note") || "Ghi chú"} name="note" value={formData.note} onChange={onInputChange} className="md:col-span-2" />
                </div>
            </FormSection>

            <FormSection title={translate("checkout.billing_info") || "Thông tin thanh toán"} icon={MapPin} iconColor="orange">
                <FormCheckbox name="billingSameAsShipping" label={translate("checkout.billing_same_as_shipping") || "Địa chỉ thanh toán giống địa chỉ giao hàng"} checked={formData.billingSameAsShipping} onChange={onBillingToggle} className="mb-6" />

                {!formData.billingSameAsShipping && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput label={translate("checkout.first_name") || "Họ"} name="billingFirstName" required value={formData.billingFirstName} onChange={onInputChange} />
                        <FormInput label={translate("checkout.last_name") || "Tên"} name="billingLastName" required value={formData.billingLastName} onChange={onInputChange} />
                        <FormInput label={translate("checkout.email") || "Email"} name="billingEmail" type="email" required value={formData.billingEmail} onChange={onInputChange} />
                        <FormInput label={translate("checkout.phone") || "Số điện thoại"} name="billingPhone" type="tel" required value={formData.billingPhone} onChange={onInputChange} />
                        <FormInput label={translate("checkout.address") || "Địa chỉ"} name="billingAddress" required value={formData.billingAddress} onChange={onInputChange} className="md:col-span-2" />
                        <FormInput label={translate("checkout.city") || "Thành phố"} name="billingCity" required value={formData.billingCity} onChange={onInputChange} />
                        <FormSelect label={translate("checkout.country") || "Quốc gia"} name="billingCountry" required value={formData.billingCountry} onChange={onInputChange} options={Object.entries(countries)} placeholder={translate("checkout.select_country") || "Chọn quốc gia"} />

                        {formData.billingCountry === "vn" && (
                            <>
                                <FormSelect label={translate("checkout.state") || "Tỉnh/Thành phố"} name="billingState" required value={formData.billingState} onChange={onInputChange} options={Object.entries(states)} placeholder={translate("checkout.select_state") || "Chọn tỉnh/thành phố"} />
                                {formData.billingState && <FormSelect label={translate("checkout.region") || "Quận/Huyện"} name="billingRegion" value={formData.billingRegion} onChange={onInputChange} options={Object.entries(regions)} placeholder={translate("checkout.select_region") || "Chọn quận/huyện"} />}
                                {formData.billingRegion && <FormSelect label={translate("checkout.sub_region") || "Phường/Xã"} name="billingSubRegion" value={formData.billingSubRegion} onChange={onInputChange} options={Object.entries(subRegions)} placeholder={translate("checkout.select_sub_region") || "Chọn phường/xã"} />}
                            </>
                        )}
                    </div>
                )}
            </FormSection>

            <FormSection title={translate("checkout.payment_method") || "Phương thức thanh toán"} icon={CreditCard} iconColor="purple">
                <div className="space-y-4">
                    {paymentMethods.map((method) => (
                        <FormRadio key={method.id} name="paymentMethod" value={method.id} label={method.name} checked={String(formData.paymentMethod) === String(method.id)} onChange={onPaymentChange} accessory={method.icon === "truck" ? <Truck className="text-blue-600" /> : method.icon === "credit-card" ? <CreditCard className="text-blue-600" /> : null} />
                    ))}
                </div>
            </FormSection>

            {shippingMethods.length > 0 && (
                <FormSection title={translate("checkout.shipping_method") || "Phương thức vận chuyển"} icon={Package} iconColor="green">
                    <div className="space-y-4">
                        {shippingMethods.map((method) => (
                            <FormRadio key={method.id} name="shippingMethod" value={method.id} label={method.name} checked={String(formData.shippingMethod) === String(method.id)} onChange={onShippingChange} accentColor="green">
                                {method.settings?.cost && <p className="text-sm text-green-600 font-medium">{formatCurrency(method.settings.cost)}</p>}
                            </FormRadio>
                        ))}
                    </div>
                </FormSection>
            )}
        </div>
    )
}

export default CheckoutForm
