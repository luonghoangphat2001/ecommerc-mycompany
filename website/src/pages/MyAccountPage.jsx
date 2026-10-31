import React, { useState, useEffect } from "react"
import { useSearchParams } from "react-router-dom"
import useAuthStore from "../features/auth/store/useAuthStore"
import withAuth from "../features/auth/hoc/withAuth"
import AccountSidebar from "../features/account/components/AccountSidebar"
import OrderHistory from "../features/order/components/OrderHistory"
import ProfileDetails from "../features/account/components/ProfileDetails"
import AddressBook from "../features/account/components/AddressBook"
import ChangePassword from "../features/account/components/ChangePassword"
import OrderTracking from "../features/order/components/OrderTracking"
import OrderDetail from "../features/order/components/OrderDetail"
import orderService from "../features/order/services/orderService"
import { unwrapApiList, unwrapApiObject } from "../api/apiResponse"
import useSettingsStore from "../store/useSettingsStore"
import {
    AccountDashboard,
    PaymentHistory,
    RefundHistory,
    CouponHistory,
    LoyaltyPoints,
} from "../features/account/components/AccountPlaceholders"

const MyAccountPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { user, logout } = useAuthStore()
    const [searchParams] = useSearchParams()
    const initialTab = searchParams.get("tab") || "dashboard"
    const [activeTab, setActiveTab] = useState(initialTab)
    const [selectedOrder, setSelectedOrder] = useState(null)
    const [orders, setOrders] = useState([])

    useEffect(() => {
        const tabParam = searchParams.get("tab")
        if (tabParam) {
            setActiveTab(tabParam)
        }
    }, [searchParams])

    useEffect(() => {
        if (activeTab === "orders" || activeTab === "dashboard") {
            fetchOrders()
        }
    }, [activeTab])

    const fetchOrders = async () => {
        try {
            const response = await orderService.getAll({ include: "items,payments" })
            const ordersData = unwrapApiList(response, [])
            setOrders(ordersData)
        } catch (error) {
            console.error("Failed to fetch orders:", error)
            setOrders([])
        }
    }

    const handleViewOrder = async (order) => {
        try {
            const response = await orderService.getById(order.id)
            setSelectedOrder(unwrapApiObject(response))
            setActiveTab("order-detail")
        } catch (error) {
            console.error("Failed to fetch order detail:", error)
        }
    }

    return (
        <div className="w-full max-w-6xl mx-auto py-2">
            <div className="flex flex-col lg:flex-row gap-8 items-start">
                <AccountSidebar
                    user={user}
                    onLogout={logout}
                    activeTab={activeTab === "order-detail" ? "orders" : activeTab}
                    setActiveTab={(tab) => {
                        setActiveTab(tab)
                        setSelectedOrder(null)
                    }}
                />

                <div className="flex-1 w-full min-w-0">
                    {activeTab === "dashboard" && (
                        <AccountDashboard
                            user={user}
                            ordersCount={orders.length}
                            onNavigateTab={setActiveTab}
                            translate={translate}
                        />
                    )}
                    {activeTab === "orders" && <OrderHistory orders={orders} onViewOrder={handleViewOrder} />}
                    {activeTab === "profile" && <ProfileDetails user={user} />}
                    {activeTab === "address" && <AddressBook />}
                    {activeTab === "password" && <ChangePassword />}
                    {activeTab === "tracking" && <OrderTracking />}
                    {activeTab === "order-detail" && (
                        <OrderDetail order={selectedOrder} onBack={() => setActiveTab("orders")} />
                    )}
                    {activeTab === "payments" && <PaymentHistory translate={translate} />}
                    {activeTab === "refunds" && <RefundHistory translate={translate} />}
                    {activeTab === "coupons" && <CouponHistory translate={translate} />}
                    {activeTab === "loyalty" && <LoyaltyPoints translate={translate} />}
                </div>
            </div>
        </div>
    )
}

export default withAuth(MyAccountPage)
