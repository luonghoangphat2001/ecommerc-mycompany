import React from "react"
import { Banknote, Truck } from "lucide-react"
import useSettingsStore from "../../../store/useSettingsStore"

/**
 * Strategy Pattern for Payment Methods
 * Each strategy renders the UI specific to a payment method.
 * New payment methods can be added here without modifying CheckoutForm.
 */

const CodStrategy = () => {
    const translate = useSettingsStore((state) => state.translate)
    return (
        <div className="mt-4 p-5 bg-amber-50/80 backdrop-blur-sm rounded-2xl border border-amber-200/60">
            <div className="flex items-center gap-3 mb-2">
                <Truck className="w-5 h-5 text-amber-600" />
                <h4 className="font-semibold text-amber-800">{translate("checkout.cod_title") || "Thanh toán khi nhận hàng"}</h4>
            </div>
            <p className="text-sm text-amber-700">
                {translate("checkout.cod_description") || "Bạn sẽ thanh toán bằng tiền mặt cho nhân viên giao hàng khi nhận được đơn hàng."}
            </p>
        </div>
    )
}

const BankTransferStrategy = () => {
    const translate = useSettingsStore((state) => state.translate)
    return (
        <div className="mt-4 p-5 bg-blue-50/80 backdrop-blur-sm rounded-2xl border border-blue-200/60">
            <div className="flex items-center gap-3 mb-2">
                <Banknote className="w-5 h-5 text-blue-600" />
                <h4 className="font-semibold text-blue-800">{translate("checkout.bank_transfer_title") || "Chuyển khoản ngân hàng"}</h4>
            </div>
            <p className="text-sm text-blue-700 mb-3">
                {translate("checkout.bank_transfer_description") || "Vui lòng chuyển khoản theo thông tin bên dưới. Đơn hàng sẽ được xử lý sau khi chúng tôi xác nhận thanh toán."}
            </p>
            <div className="bg-white/70 rounded-xl p-4 space-y-1 text-sm">
                <p><span className="font-medium text-slate-600">{translate("checkout.bank_name") || "Ngân hàng"}:</span> <span className="text-slate-800">BIDV</span></p>
                <p><span className="font-medium text-slate-600">{translate("checkout.account_number") || "Số tài khoản"}:</span> <span className="text-slate-800 font-mono">31410001234567</span></p>
                <p><span className="font-medium text-slate-600">{translate("checkout.account_name") || "Chủ tài khoản"}:</span> <span className="text-slate-800">MY ECOMMERCE CO LTD</span></p>
            </div>
        </div>
    )
}

/**
 * Strategy Map: maps payment method ID -> Component
 * To add a new payment method, simply add a new entry here.
 */
export const paymentStrategiesMap = {
    cod: CodStrategy,
    bank_transfer: BankTransferStrategy,
}

export default paymentStrategiesMap
