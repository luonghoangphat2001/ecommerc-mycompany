import React from "react"
import { Package, CreditCard, Tag, Star } from "lucide-react"
import EmptyState from "../../../components/common/EmptyState"

export const AccountDashboard = ({ user, translate }) => (
    <div className="space-y-6">
        <h2 className="text-2xl font-bold text-slate-800">Tổng quan tài khoản</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 className="font-bold text-slate-800 mb-2">Thông tin cá nhân</h3>
                <p className="text-slate-600">{user?.name}</p>
                <p className="text-slate-600">{user?.email}</p>
            </div>
            {/* Can add more summary widgets here */}
        </div>
    </div>
)

export const PaymentHistory = ({ translate }) => (
    <div className="space-y-6">
        <h2 className="text-2xl font-bold text-slate-800">Lịch sử thanh toán</h2>
        <EmptyState icon={CreditCard} title="Chưa có thanh toán nào" description="Bạn chưa thực hiện giao dịch thanh toán nào." />
    </div>
)

export const RefundHistory = ({ translate }) => (
    <div className="space-y-6">
        <h2 className="text-2xl font-bold text-slate-800">Yêu cầu hoàn tiền</h2>
        <EmptyState icon={Package} title="Không có yêu cầu hoàn tiền" description="Bạn không có yêu cầu hoàn tiền nào đang xử lý." />
    </div>
)

export const CouponHistory = ({ translate }) => (
    <div className="space-y-6">
        <h2 className="text-2xl font-bold text-slate-800">Mã giảm giá của tôi</h2>
        <EmptyState icon={Tag} title="Chưa có mã giảm giá" description="Bạn hiện không có mã giảm giá nào." />
    </div>
)

export const LoyaltyPoints = ({ translate }) => (
    <div className="space-y-6">
        <h2 className="text-2xl font-bold text-slate-800">Điểm thưởng Loyalty</h2>
        <EmptyState icon={Star} title="Chưa có điểm thưởng" description="Tham gia mua sắm để tích lũy điểm thưởng nhé." />
    </div>
)
