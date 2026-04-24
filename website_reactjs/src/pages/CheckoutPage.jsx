import React from 'react';
import { useNavigate } from 'react-router-dom';
import { CheckCircle2 } from 'lucide-react';
import useCartStore from '../features/cart/store/useCartStore';
import useAuthStore from '../features/auth/store/useAuthStore';
import useCheckoutForm from '../features/checkout/hooks/useCheckoutForm';
import CheckoutForm from '../features/checkout/components/CheckoutForm';
import OrderSummary from '../features/checkout/components/OrderSummary';

const CheckoutPage = () => {
  const navigate = useNavigate();
  const { items, getCartTotal, clearCart } = useCartStore();
  const { user } = useAuthStore();
  
  const { 
    formData, 
    isSubmitting, 
    isSuccess, 
    handleSubmit, 
    handleInputChange 
  } = useCheckoutForm(user, clearCart);

  if (isSuccess) {
    return (
      <div className="w-full py-20 flex flex-col items-center justify-center text-center animate-in fade-in zoom-in duration-500">
        <div className="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center text-green-500 mb-6 shadow-xl shadow-green-100">
          <CheckCircle2 size={48} />
        </div>
        <h2 className="text-3xl font-black text-slate-900 mb-2 tracking-tight">Đặt hàng thành công!</h2>
        <p className="text-slate-500 mb-10 max-w-sm mx-auto">
          Cảm ơn bạn đã tin dùng NovaStore. Mã đơn hàng của bạn là #NS-9923. Chúng tôi sẽ sớm liên hệ xác nhận.
        </p>
        <button 
          onClick={() => navigate('/')}
          className="bg-slate-900 text-white font-bold px-10 py-4 rounded-2xl hover:bg-blue-600 transition-all shadow-xl shadow-slate-200"
        >
          Quay về trang chủ
        </button>
      </div>
    );
  }

  return (
    <div className="w-full max-w-6xl mx-auto">
      <h1 className="text-3xl font-black text-slate-900 mb-10 tracking-tight">Thanh Toán</h1>
      
      <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <CheckoutForm 
          formData={formData} 
          onInputChange={handleInputChange} 
          isSubmitting={isSubmitting} 
        />
        
        <OrderSummary 
          items={items} 
          total={getCartTotal()} 
          isSubmitting={isSubmitting} 
        />
      </form>
    </div>
  );
};

export default CheckoutPage;


