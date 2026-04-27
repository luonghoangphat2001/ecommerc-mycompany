import React from 'react';
import { ArrowLeft, Package, Calendar, CreditCard, MapPin } from 'lucide-react';
import { formatCurrency } from '../../../utils/format';
import Card from '../../../components/common/Card';

const OrderDetail = ({ order, onBack }) => {
  if (!order) return null;

  const items = order.items || [];
  const shippingAddress = order.shipping_address || {};
  const billingAddress = order.billing_address || {};

  const formatAddress = (addr) => {
    const parts = [];
    if (addr.street) parts.push(addr.street);
    if (addr.ward) parts.push(addr.ward);
    if (addr.state) parts.push(addr.state);
    if (addr.city) parts.push(addr.city);
    if (addr.country) parts.push(addr.country);
    return parts.join(', ');
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-right duration-300">
      <button 
        onClick={onBack}
        className="flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors font-bold mb-4"
      >
        <ArrowLeft size={20} />
        Quay lại danh sách
      </button>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-8">
          <Card className="shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
            <Card.Header>
              <div className="flex justify-between items-center">
                <Card.Title>Chi tiết sản phẩm</Card.Title>
                <span className="text-sm font-bold text-slate-500">Mã đơn: {order.id}</span>
              </div>
            </Card.Header>
            <Card.Body>
              <div className="space-y-6">
                {items.map((item) => (
                  <div key={item.id} className="flex gap-6 pb-6 border-b border-slate-100 last:border-0 last:pb-0">
                    <div className="w-20 h-20 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0">
                      <img 
                        src={item.product?.image?.url || 'https://via.placeholder.com/200'} 
                        alt={item.product?.name || 'Product'} 
                        className="w-full h-full object-cover" 
                      />
                    </div>
                    <div className="flex-1">
                      <h4 className="font-bold text-slate-900">{item.product?.name || 'Sản phẩm'}</h4>
                      <p className="text-sm text-slate-500">Số lượng: {item.qty}</p>
                      <p className="text-blue-600 font-black mt-1">{formatCurrency(item.unit_price)}</p>
                    </div>
                  </div>
                ))}
              </div>
            </Card.Body>
          </Card>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <Card className="shadow-sm border-slate-100">
              <Card.Header className="p-6 border-b border-slate-50">
                <div className="flex items-center gap-3 text-slate-900 font-bold">
                  <CreditCard size={18} className="text-blue-600" />
                  Địa chỉ thanh toán
                </div>
              </Card.Header>
              <Card.Body className="p-6 text-sm font-medium text-slate-600 leading-relaxed">
                <p className="font-bold text-slate-900 mb-2">{billingAddress.first_name} {billingAddress.last_name}</p>
                {billingAddress.email && <p className="mb-1">{billingAddress.email}</p>}
                {billingAddress.phone && <p className="mb-2">{billingAddress.phone}</p>}
                <p>{formatAddress(billingAddress)}</p>
              </Card.Body>
            </Card>

            <Card className="shadow-sm border-slate-100">
              <Card.Header className="p-6 border-b border-slate-50">
                <div className="flex items-center gap-3 text-slate-900 font-bold">
                  <MapPin size={18} className="text-blue-600" />
                  Địa chỉ giao hàng
                </div>
              </Card.Header>
              <Card.Body className="p-6 text-sm font-medium text-slate-600 leading-relaxed">
                <p className="font-bold text-slate-900 mb-2">{shippingAddress.first_name} {shippingAddress.last_name}</p>
                {shippingAddress.phone && <p className="mb-2">{shippingAddress.phone}</p>}
                <p>{formatAddress(shippingAddress)}</p>
              </Card.Body>
            </Card>
          </div>

          <Card className="shadow-sm border-slate-100">
            <Card.Header className="p-6 border-b border-slate-50">
              <div className="flex items-center gap-3 text-slate-900 font-bold">
                <CreditCard size={18} className="text-blue-600" />
                Chi tiết thanh toán
              </div>
            </Card.Header>
            <Card.Body className="p-6 space-y-4 text-sm font-medium">
              <div className="flex justify-between text-slate-500">
                <span>Tạm tính</span>
                <span className="text-slate-900">{formatCurrency(order.subtotal || order.total)}</span>
              </div>
              {order.tax_amount > 0 && (
                <div className="flex justify-between text-slate-500">
                  <span>Thuế</span>
                  <span className="text-slate-900">{formatCurrency(order.tax_amount)}</span>
                </div>
              )}
              <div className="flex justify-between text-slate-500">
                <span>Vận chuyển</span>
                <span className="text-green-600">{order.shipping_cost > 0 ? formatCurrency(order.shipping_cost) : 'Miễn phí'}</span>
              </div>
              <div className="pt-4 border-t border-slate-100 flex justify-between items-center text-base">
                <span className="font-bold text-slate-900">Tổng cộng</span>
                <span className="font-black text-blue-600">{formatCurrency(order.total)}</span>
              </div>
            </Card.Body>
          </Card>
        </div>

        <div className="space-y-8">
          <Card className="shadow-sm border-slate-100">
            <Card.Header className="p-6 border-b border-slate-50">
              <div className="flex items-center gap-3 text-slate-900 font-bold">
                <Calendar size={18} className="text-blue-600" />
                Thông tin đơn hàng
              </div>
            </Card.Header>
            <Card.Body className="p-6 space-y-4">
              <div>
                <p className="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-1">Ngày đặt</p>
                <p className="font-bold text-slate-900">{new Date(order.created_at).toLocaleDateString('vi-VN')}</p>
              </div>
              <div>
                <p className="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-1">Trạng thái</p>
                <span className={`inline-block px-3 py-1 rounded-full text-xs font-bold ${
                  order.status === 'Đang xử lý' ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-600'
                }`}>
                  {order.status}
                </span>
              </div>
            </Card.Body>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default OrderDetail;
