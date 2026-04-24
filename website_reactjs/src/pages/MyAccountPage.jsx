import React, { useState } from 'react';
import useAuthStore from '../features/auth/store/useAuthStore';
import withAuth from '../features/auth/hoc/withAuth';
import AccountSidebar from '../features/account/components/AccountSidebar';
import OrderHistory from '../features/order/components/OrderHistory';
import ProfileDetails from '../features/account/components/ProfileDetails';
import ChangePassword from '../features/account/components/ChangePassword';
import OrderTracking from '../features/order/components/OrderTracking';
import OrderDetail from '../features/order/components/OrderDetail';

const MyAccountPage = () => {
  const { user, logout } = useAuthStore();
  const [activeTab, setActiveTab] = useState('orders');
  const [selectedOrder, setSelectedOrder] = useState(null);

  const orders = [
    { id: '#NS-9923', date: '24/04/2026', total: 1200000, status: 'Đang xử lý' },
    { id: '#NS-8812', date: '12/03/2026', total: 2500000, status: 'Đã giao hàng' },
  ];

  const handleViewOrder = (order) => {
    setSelectedOrder(order);
    setActiveTab('order-detail');
  };

  return (
    <div className="w-full max-w-6xl mx-auto py-10">
      <div className="flex flex-col md:flex-row gap-10">
        <AccountSidebar 
          user={user} 
          onLogout={logout} 
          activeTab={activeTab === 'order-detail' ? 'orders' : activeTab}
          setActiveTab={(tab) => {
            setActiveTab(tab);
            setSelectedOrder(null);
          }}
        />
        
        <main className="flex-1">
          {activeTab === 'orders' && <OrderHistory orders={orders} onViewOrder={handleViewOrder} />}
          {activeTab === 'profile' && <ProfileDetails user={user} />}
          {activeTab === 'password' && <ChangePassword />}
          {activeTab === 'tracking' && <OrderTracking />}
          {activeTab === 'order-detail' && <OrderDetail order={selectedOrder} onBack={() => setActiveTab('orders')} />}
        </main>
      </div>
    </div>
  );
};

export default withAuth(MyAccountPage);




