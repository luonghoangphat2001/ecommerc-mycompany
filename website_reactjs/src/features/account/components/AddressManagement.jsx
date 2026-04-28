import React, { useState, useEffect } from "react"
import { MapPin, ChevronDown } from "lucide-react"
import Card from "../../../components/common/Card"
import useSettingsStore from "../../../store/useSettingsStore"
import useAddressSelect from "../../address/hooks/useAddressSelect"

const inputClass = "w-full px-5 py-3.5 bg-white/50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
const labelClass = "block text-sm font-semibold text-slate-700 mb-2 ml-1"

const AddressManagement = ({ user }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { countries, states, regions, subRegions, loading, fetchStates, fetchRegions, fetchSubRegions } = useAddressSelect()

    const [addressData, setAddressData] = useState({
        // Shipping address
        shipping_first_name: user?.shipping_address?.first_name || user?.first_name || "",
        shipping_last_name: user?.shipping_address?.last_name || user?.last_name || "",
        shipping_phone: user?.shipping_address?.phone || user?.phone || "",
        shipping_address: user?.shipping_address?.street || user?.address || "",
        shipping_city: user?.shipping_address?.city || user?.city || "",
        shipping_country: user?.shipping_address?.country || user?.country || "VN",
        shipping_state: user?.shipping_address?.state || user?.state || "",
        shipping_region: user?.shipping_address?.region || user?.region || "",
        shipping_sub_region: user?.shipping_address?.sub_region || user?.sub_region || "",
        // Billing address
        billing_same_as_shipping: !user?.billing_address,
        billing_first_name: user?.billing_address?.first_name || "",
        billing_last_name: user?.billing_address?.last_name || "",
        billing_phone: user?.billing_address?.phone || "",
        billing_email: user?.billing_address?.email || user?.email || "",
        billing_address: user?.billing_address?.street || "",
        billing_city: user?.billing_address?.city || "",
        billing_state: user?.billing_address?.state || "",
        billing_region: user?.billing_address?.region || "",
        billing_sub_region: user?.billing_address?.sub_region || "",
        billing_country: user?.billing_address?.country || "VN",
    })

    // Fetch states when shipping country changes
    useEffect(() => {
        if (addressData.shipping_country) {
            fetchStates(addressData.shipping_country)
        }
    }, [addressData.shipping_country, fetchStates])

    // Fetch regions when shipping state changes
    useEffect(() => {
        if (addressData.shipping_country && addressData.shipping_state) {
            fetchRegions(addressData.shipping_country, addressData.shipping_state)
        }
    }, [addressData.shipping_country, addressData.shipping_state, fetchRegions])

    // Fetch sub-regions when shipping region changes
    useEffect(() => {
        if (addressData.shipping_country && addressData.shipping_state && addressData.shipping_region) {
            fetchSubRegions(addressData.shipping_country, addressData.shipping_state, addressData.shipping_region)
        }
    }, [addressData.shipping_country, addressData.shipping_state, addressData.shipping_region, fetchSubRegions])

    return (
        <div className="space-y-8">
            <Card className="p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
                <Card.Header className="px-0 pt-0 border-none">
                    <Card.Title className="text-2xl font-black tracking-tight flex items-center gap-3">
                        <MapPin size={28} className="text-blue-600" />
                        {translate("account.addresses_title")}
                    </Card.Title>
                </Card.Header>

                <Card.Body className="px-0 pb-0">
                    <form className="space-y-8">
                        {/* Shipping Address */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-bold text-slate-800">{translate("checkout.shipping_info")}</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <label className={labelClass}>{translate("checkout.first_name")}</label>
                                    <input type="text" value={addressData.shipping_first_name} onChange={(e) => setAddressData({ ...addressData, shipping_first_name: e.target.value })} className={inputClass} />
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClass}>{translate("checkout.last_name")}</label>
                                    <input type="text" value={addressData.shipping_last_name} onChange={(e) => setAddressData({ ...addressData, shipping_last_name: e.target.value })} className={inputClass} />
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClass}>{translate("account.phone")}</label>
                                    <input type="tel" value={addressData.shipping_phone} onChange={(e) => setAddressData({ ...addressData, shipping_phone: e.target.value })} className={inputClass} />
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClass}>{translate("checkout.country")}</label>
                                    <div className="relative">
                                        <select name="shipping_country" value={addressData.shipping_country} onChange={(e) => setAddressData({ ...addressData, shipping_country: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                            <option value="">{translate("checkout.select_country") || "Chọn quốc gia"}</option>
                                            {Object.entries(countries).map(([code, name]) => (
                                                <option key={code} value={code}>
                                                    {name}
                                                </option>
                                            ))}
                                        </select>
                                        <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                    </div>
                                </div>
                                {addressData.shipping_country === "VN" && (
                                    <>
                                        <div className="space-y-2">
                                            <label className={labelClass}>{translate("checkout.state")}</label>
                                            <div className="relative">
                                                <select name="shipping_state" value={addressData.shipping_state} onChange={(e) => setAddressData({ ...addressData, shipping_state: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                                    <option value="">{translate("checkout.select_state") || "Chọn tỉnh/thành phố"}</option>
                                                    {Object.entries(states).map(([id, name]) => (
                                                        <option key={id} value={id}>
                                                            {name}
                                                        </option>
                                                    ))}
                                                </select>
                                                <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                            </div>
                                        </div>
                                        {addressData.shipping_state && (
                                            <div className="space-y-2">
                                                <label className={labelClass}>{translate("checkout.region")}</label>
                                                <div className="relative">
                                                    <select name="shipping_region" value={addressData.shipping_region} onChange={(e) => setAddressData({ ...addressData, shipping_region: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                                        <option value="">{translate("checkout.select_region") || "Chọn quận/huyện"}</option>
                                                        {Object.entries(regions).map(([id, name]) => (
                                                            <option key={id} value={id}>
                                                                {name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                                </div>
                                            </div>
                                        )}
                                        {addressData.shipping_region && (
                                            <div className="space-y-2">
                                                <label className={labelClass}>{translate("checkout.sub_region")}</label>
                                                <div className="relative">
                                                    <select name="shipping_sub_region" value={addressData.shipping_sub_region} onChange={(e) => setAddressData({ ...addressData, shipping_sub_region: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                                        <option value="">{translate("checkout.select_sub_region") || "Chọn phường/xã"}</option>
                                                        {Object.entries(subRegions).map(([id, name]) => (
                                                            <option key={id} value={id}>
                                                                {name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                                </div>
                                            </div>
                                        )}
                                    </>
                                )}
                                <div className="space-y-2 md:col-span-2">
                                    <label className={labelClass}>{translate("checkout.address")}</label>
                                    <input type="text" value={addressData.shipping_address} onChange={(e) => setAddressData({ ...addressData, shipping_address: e.target.value })} placeholder={translate("checkout.address_placeholder")} className={inputClass} />
                                </div>
                            </div>
                        </div>

                        {/* Billing Address Toggle */}
                        <div className="flex items-center gap-3">
                            <input
                                type="checkbox"
                                id="billingSameAsShipping"
                                checked={addressData.billing_same_as_shipping}
                                onChange={(e) => {
                                    const checked = e.target.checked
                                    setAddressData((prev) => {
                                        const newState = { ...prev, billing_same_as_shipping: checked }
                                        if (checked) {
                                            newState.billing_first_name = prev.shipping_first_name
                                            newState.billing_last_name = prev.shipping_last_name
                                            newState.billing_phone = prev.shipping_phone
                                            newState.billing_email = user?.email || ""
                                            newState.billing_address = prev.shipping_address
                                            newState.billing_city = prev.shipping_city
                                            newState.billing_country = prev.shipping_country
                                            newState.billing_state = prev.shipping_state
                                            newState.billing_region = prev.shipping_region
                                            newState.billing_sub_region = prev.shipping_sub_region
                                        }
                                        return newState
                                    })
                                }}
                                className="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label htmlFor="billingSameAsShipping" className="text-sm font-medium text-slate-700">
                                {translate("checkout.billing_same_as_shipping")}
                            </label>
                        </div>

                        {/* Billing Address */}
                        {!addressData.billing_same_as_shipping && (
                            <div className="space-y-4">
                                <h3 className="text-lg font-bold text-slate-800">{translate("checkout.billing_info")}</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("checkout.first_name")}</label>
                                        <input type="text" value={addressData.billing_first_name} onChange={(e) => setAddressData({ ...addressData, billing_first_name: e.target.value })} className={inputClass} />
                                    </div>
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("checkout.last_name")}</label>
                                        <input type="text" value={addressData.billing_last_name} onChange={(e) => setAddressData({ ...addressData, billing_last_name: e.target.value })} className={inputClass} />
                                    </div>
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("account.phone")}</label>
                                        <input type="tel" value={addressData.billing_phone} onChange={(e) => setAddressData({ ...addressData, billing_phone: e.target.value })} className={inputClass} />
                                    </div>
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("account.email")}</label>
                                        <input type="email" value={addressData.billing_email} onChange={(e) => setAddressData({ ...addressData, billing_email: e.target.value })} className={inputClass} />
                                    </div>
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("checkout.country")}</label>
                                        <div className="relative">
                                            <select name="billing_country" value={addressData.billing_country} onChange={(e) => setAddressData({ ...addressData, billing_country: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                                <option value="">{translate("checkout.select_country") || "Chọn quốc gia"}</option>
                                                {Object.entries(countries).map(([code, name]) => (
                                                    <option key={code} value={code}>
                                                        {name}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("checkout.state")}</label>
                                        <div className="relative">
                                            <select name="billing_state" value={addressData.billing_state} onChange={(e) => setAddressData({ ...addressData, billing_state: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                                <option value="">{translate("checkout.select_state") || "Chọn tỉnh/thành phố"}</option>
                                                {Object.entries(states).map(([id, name]) => (
                                                    <option key={id} value={id}>
                                                        {name}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("checkout.region")}</label>
                                        <div className="relative">
                                            <select name="billing_region" value={addressData.billing_region} onChange={(e) => setAddressData({ ...addressData, billing_region: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                                <option value="">{translate("checkout.select_region") || "Chọn quận/huyện"}</option>
                                                {Object.entries(regions).map(([id, name]) => (
                                                    <option key={id} value={id}>
                                                        {name}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <label className={labelClass}>{translate("checkout.sub_region")}</label>
                                        <div className="relative">
                                            <select name="billing_sub_region" value={addressData.billing_sub_region} onChange={(e) => setAddressData({ ...addressData, billing_sub_region: e.target.value })} className={`${inputClass} appearance-none pr-10`}>
                                                <option value="">{translate("checkout.select_sub_region") || "Chọn phường/xã"}</option>
                                                {Object.entries(subRegions).map(([id, name]) => (
                                                    <option key={id} value={id}>
                                                        {name}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                        </div>
                                    </div>
                                    <div className="space-y-2 md:col-span-2">
                                        <label className={labelClass}>{translate("checkout.address")}</label>
                                        <input type="text" value={addressData.billing_address} onChange={(e) => setAddressData({ ...addressData, billing_address: e.target.value })} placeholder={translate("checkout.address_placeholder")} className={inputClass} />
                                    </div>
                                </div>
                            </div>
                        )}

                        <button className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-2xl transition-all shadow-lg shadow-blue-100 hover:shadow-blue-200">{translate("account.save_address")}</button>
                    </form>
                </Card.Body>
            </Card>
        </div>
    )
}

export default AddressManagement
