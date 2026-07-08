import React, { useState, useEffect } from "react"
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

const MyAccountPage = () => {
    const { user, logout } = useAuthStore()
    const [activeTab, setActiveTab] = useState("orders")
    const [selectedOrder, setSelectedOrder] = useState(null)
    const [orders, setOrders] = useState([])
    const [loading, setLoading] = useState(false)

    useEffect(() => {
        if (activeTab === "orders") {
            fetchOrders()
        }
    }, [activeTab])

    const fetchOrders = async () => {
        try {
            setLoading(true)
            const response = await orderService.getAll()
            // API returns { success: true, data: [], meta: {...} }
            // axiosClient already unwraps response.data
            const ordersData = response.data || []
            setOrders(ordersData)
            console.log("Orders:", ordersData)
        } catch (error) {
            console.error("Failed to fetch orders:", error)
            setOrders([])
        } finally {
            setLoading(false)
        }
    }

    const handleViewOrder = async (order) => {
        try {
            const response = await orderService.getById(order.id)
            setSelectedOrder(response.data)
            setActiveTab("order-detail")
        } catch (error) {
            console.error("Failed to fetch order detail:", error)
        }
    }

    return (
        <div className="w-full max-w-6xl mx-auto py-10">
            <div className="flex flex-col md:flex-row gap-10">
                <AccountSidebar
                    user={user}
                    onLogout={logout}
                    activeTab={activeTab === "order-detail" ? "orders" : activeTab}
                    setActiveTab={(tab) => {
                        setActiveTab(tab)
                        setSelectedOrder(null)
                    }}
                />

                <main className="flex-1">
                    {activeTab === "orders" && <OrderHistory orders={orders} onViewOrder={handleViewOrder} />}
                    {activeTab === "profile" && <ProfileDetails user={user} />}
                    {activeTab === "address" && <AddressBook />}
                    {activeTab === "password" && <ChangePassword />}
                    {activeTab === "tracking" && <OrderTracking />}
                    {activeTab === "order-detail" && <OrderDetail order={selectedOrder} onBack={() => setActiveTab("orders")} />}
                </main>
            </div>
        </div>
    )
}

export default withAuth(MyAccountPage)
