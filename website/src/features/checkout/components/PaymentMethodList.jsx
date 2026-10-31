import React from "react"
import { Banknote, Smartphone, QrCode, CreditCard } from "lucide-react"
import useSettingsStore from "../../../store/useSettingsStore"

const METHOD_ICONS = {
    cod: Banknote,
    momo: Smartphone,
    vnpay: QrCode,
    card: CreditCard,
}

const PaymentMethodList = ({ paymentMethods = [], selectedMethod, onMethodChange }) => {
    const translate = useSettingsStore((state) => state.translate)

    return (
        <div className="space-y-3">
            {paymentMethods.map((method) => {
                const Icon = METHOD_ICONS[method.code] || CreditCard
                const isSelected = selectedMethod === method.id
                return (
                    <div
                        key={method.id}
                        className={`flex items-center gap-3.5 p-4 border rounded-xl cursor-pointer transition-all ${
                            isSelected
                                ? "border-[#004687] bg-[#004687]/[0.03] ring-1 ring-[#004687]"
                                : "border-slate-200 hover:border-slate-300 bg-white"
                        }`}
                        onClick={() => onMethodChange(method.id)}
                    >
                        <div className="w-9 h-9 rounded-lg bg-slate-100 text-[#004687] flex items-center justify-center flex-shrink-0">
                            <Icon size={18} />
                        </div>
                        <div className="flex-1 min-w-0">
                            <div className="text-sm font-semibold text-slate-900">
                                {method.name || translate(`checkout.${method.code}`)}
                            </div>
                            {method.description && (
                                <div className="text-xs text-slate-500 mt-0.5">{method.description}</div>
                            )}
                        </div>
                        <div
                            className={`w-4 h-4 border rounded-full flex items-center justify-center ${
                                isSelected ? "border-[#004687]" : "border-slate-300"
                            }`}
                        >
                            {isSelected && <div className="w-2 h-2 bg-[#004687] rounded-full" />}
                        </div>
                    </div>
                )
            })}
        </div>
    )
}

export default PaymentMethodList
