import React from 'react';
import { ArrowLeft, Package, Calendar, CreditCard, MapPin } from 'lucide-react';
import { formatCurrency } from '../../../utils/format';
import Card from '../../../components/common/Card';

const OrderDetail = ({ order, onBack }) => {
  if (!order) return null;

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
                {[1, 2].map((i) => (
                  <div key={i} className="flex gap-6 pb-6 border-b border-slate-100 last:border-0 last:pb-0">
                    <div className="w-20 h-20 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0">
                      <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=200" alt="product" className="w-full h-full object-cover" />
                    </div>
                    <div className="flex-1">
                      <h4 className="font-bold text-slate-900">Sản phẩm ví dụ #{i}</h4>
                      <p className="text-sm text-slate-500">Số lượng: 1</p>
                      <p className="text-blue-600 font-black mt-1">{formatCurrency(order.total / 2)}</p>
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
                  Thanh toán
                </div>
              </Card.Header>
              <Card.Body className="p-6 space-y-4 text-sm font-medium">
                <div className="flex justify-between text-slate-500">
                  <span>Tạm tính</span>
                  <span className="text-slate-900">{formatCurrency(order.total)}</span>
                </div>
                <div className="flex justify-between text-slate-500">
                  <span>Vận chuyển</span>
                  <span className="text-green-600">Miễn phí</span>
                </div>
                <div className="pt-4 border-t border-slate-100 flex justify-between items-center text-base">
                  <span className="font-bold text-slate-900">Tổng cộng</span>
                  <span className="font-black text-blue-600">{formatCurrency(order.total)}</span>
                </div>
              </Card.Body>
            </Card>

            <Card className="shadow-sm border-slate-100">
              <Card.Header className="p-6 border-b border-slate-50">
                <div className="flex items-center gap-3 text-slate-900 font-bold">
                  <MapPin size={18} className="text-blue-600" />
                  Giao hàng
                </div>
              </Card.Header>
              <Card.Body className="p-6 text-sm font-medium text-slate-600 leading-relaxed">
                <p className="font-bold text-slate-900 mb-1">Nguyễn Văn A</p>
                <p>0987 654 321</p>
                <p>123 Đường ABC, Quận 1, TP. Hồ Chí Minh</p>
              </Card.Body>
            </Card>
          </div>
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
                <p className="font-bold text-slate-900">{order.date}</p>
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
