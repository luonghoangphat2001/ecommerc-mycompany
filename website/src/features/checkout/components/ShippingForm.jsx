import React from "react"
import useSettingsStore from "../../../store/useSettingsStore"
import useFormatters from "../../../utils/useFormatters"

const ShippingForm = ({ form, shippingMethods = [], selectedShippingMethod, onShippingMethodChange }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { formatCurrency } = useFormatters()
    const {
        register,
        formState: { errors },
    } = form

    const inputClass =
        "w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687]"
    const errorClass = "text-red-600 text-xs mt-1"

    return (
        <div className="space-y-6">
            <div className="space-y-4">
                <h2 className="text-base font-bold text-[#09253E]">{translate("checkout.shipping_info")}</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            {translate("checkout.first_name")}
                        </label>
                        <input {...register("shipping_address.first_name")} className={inputClass} />
                        {errors.shipping_address?.first_name && (
                            <p className={errorClass}>{errors.shipping_address.first_name.message}</p>
                        )}
                    </div>
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            {translate("checkout.last_name")}
                        </label>
                        <input {...register("shipping_address.last_name")} className={inputClass} />
                        {errors.shipping_address?.last_name && (
                            <p className={errorClass}>{errors.shipping_address.last_name.message}</p>
                        )}
                    </div>
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            {translate("checkout.email")}
                        </label>
                        <input {...register("shipping_address.email")} type="email" className={inputClass} />
                        {errors.shipping_address?.email && (
                            <p className={errorClass}>{errors.shipping_address.email.message}</p>
                        )}
                    </div>
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            {translate("checkout.phone")}
                        </label>
                        <input {...register("shipping_address.phone")} className={inputClass} />
                        {errors.shipping_address?.phone && (
                            <p className={errorClass}>{errors.shipping_address.phone.message}</p>
                        )}
                    </div>
                    <div className="md:col-span-2">
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            {translate("checkout.address")}
                        </label>
                        <input {...register("shipping_address.address")} className={inputClass} />
                        {errors.shipping_address?.address && (
                            <p className={errorClass}>{errors.shipping_address.address.message}</p>
                        )}
                    </div>
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            {translate("checkout.city")}
                        </label>
                        <input {...register("shipping_address.city")} className={inputClass} />
                        {errors.shipping_address?.city && (
                            <p className={errorClass}>{errors.shipping_address.city.message}</p>
                        )}
                    </div>
                </div>
            </div>

            {shippingMethods.length > 0 && (
                <div className="space-y-3">
                    <h2 className="text-base font-bold text-[#09253E]">
                        {translate("checkout.shipping_method")}
                    </h2>
                    <div className="space-y-2.5">
                        {shippingMethods.map((method, index) => {
                            const methodId = method.id ?? method.method_id ?? method.code ?? method.type ?? index
                            return (
                                <label
                                    key={methodId}
                                    className={`flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition-colors ${
                                        selectedShippingMethod === methodId
                                            ? "border-[#004687] bg-[#004687]/[0.03]"
                                            : "border-slate-200 hover:bg-slate-50"
                                    }`}
                                >
                                    <input
                                        type="radio"
                                        value={methodId}
                                        {...register("shipping_method_id")}
                                        onChange={() => onShippingMethodChange(methodId)}
                                        className="accent-[#004687]"
                                    />
                                    <div className="flex-1 flex justify-between items-center text-sm">
                                        <div>
                                            <span className="font-semibold text-slate-900 block">{method.name}</span>
                                            {method.estimated_days && (
                                                <span className="text-xs text-slate-500">
                                                    Dự kiến: {method.estimated_days} ngày
                                                </span>
                                            )}
                                        </div>
                                        <span className="text-slate-800 font-semibold tabular-nums">
                                            {(method.cost === 0 || method.amount === 0)
                                                ? (translate("cart.free") || "Miễn phí")
                                                : formatCurrency(method.cost ?? method.amount ?? method.price ?? 0)}
                                        </span>
                                    </div>
                                </label>
                            )
                        })}
                    </div>
                    {errors.shipping_method_id && (
                        <p className={errorClass}>{errors.shipping_method_id.message}</p>
                    )}
                </div>
            )}
        </div>
    )
}

export default ShippingForm
