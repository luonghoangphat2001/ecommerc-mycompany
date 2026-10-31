import React from "react"
import { Banknote, Truck } from "lucide-react"
import useSettingsStore from "../../../store/useSettingsStore"

const CodStrategy = () => {
    const translate = useSettingsStore((state) => state.translate)
    return (
        <div className="mt-4 p-4 bg-amber-50/70 rounded-xl border border-amber-200/80">
            <div className="flex items-center gap-2.5 mb-1.5">
                <Truck className="w-4 h-4 text-amber-700" />
                <h4 className="text-xs font-bold uppercase tracking-wider text-amber-900">
                    {translate("checkout.cod_title") || "Cash on Delivery (COD)"}
                </h4>
            </div>
            <p className="text-xs text-amber-800 leading-relaxed">
                {translate("checkout.cod_description") ||
                    "Pay in cash directly to the courier upon inspecting and receiving your parcel."}
            </p>
        </div>
    )
}

const BankTransferStrategy = () => {
    const translate = useSettingsStore((state) => state.translate)
    return (
        <div className="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
            <div className="flex items-center gap-2.5 mb-1.5">
                <Banknote className="w-4 h-4 text-[#004687]" />
                <h4 className="text-xs font-bold uppercase tracking-wider text-[#09253E]">
                    {translate("checkout.bank_transfer_title") || "Direct Bank Settlement"}
                </h4>
            </div>
            <p className="text-xs text-slate-600 mb-3 leading-relaxed">
                {translate("checkout.bank_transfer_description") ||
                    "Transfer the order total to the corporate account below. Your order will be dispatched immediately upon settlement confirmation."}
            </p>
            <div className="bg-white rounded-lg border border-slate-200 p-3 space-y-1.5 text-xs tabular-nums">
                <div className="flex justify-between">
                    <span className="text-slate-500">{translate("checkout.bank_name") || "Bank"}:</span>
                    <span className="font-semibold text-slate-900">BIDV</span>
                </div>
                <div className="flex justify-between">
                    <span className="text-slate-500">
                        {translate("checkout.account_number") || "Account Number"}:
                    </span>
                    <span className="font-mono font-bold text-[#004687]">31410001234567</span>
                </div>
                <div className="flex justify-between">
                    <span className="text-slate-500">
                        {translate("checkout.account_name") || "Beneficiary"}:
                    </span>
                    <span className="font-semibold text-slate-900">MY ECOMMERCE CO LTD</span>
                </div>
            </div>
        </div>
    )
}

export const paymentStrategiesMap = {
    cod: CodStrategy,
    bank_transfer: BankTransferStrategy,
}

export default paymentStrategiesMap
