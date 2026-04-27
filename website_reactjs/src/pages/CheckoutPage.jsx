import React, { useMemo } from 'react';
import useCartStore from '../features/cart/store/useCartStore';
import useAuthStore from '../features/auth/store/useAuthStore';
import useSettingsStore from '../store/useSettingsStore';
import useCheckoutForm from '../features/checkout/hooks/useCheckoutForm';
import CheckoutForm from '../features/checkout/components/CheckoutForm';
import OrderSummary from '../features/checkout/components/OrderSummary';

const CheckoutPage = () => {
  const { items, getCartTotal, clearCart } = useCartStore();
  const { user } = useAuthStore();
  const t = useSettingsStore((state) => state.t);
  const getSetting = useSettingsStore((state) => state.getSetting);
  
  // Get shipping methods first for both form and summary
  const shippingMethods = getSetting('checkout.shipping_methods') || [];
  
  const { 
    formData, 
    isSubmitting, 
    handleSubmit, 
    handleInputChange,
    handlePaymentChange,
    handleShippingChange,
    handleBillingToggle
  } = useCheckoutForm(user, clearCart);

  const shippingCost = useMemo(() => {
    const selected = shippingMethods.find(m => String(m.id) === String(formData.shippingMethod));
    return selected?.settings?.cost || 0;
  }, [shippingMethods, formData.shippingMethod]);

  return (
    <div className="w-full max-w-6xl mx-auto">
      <h1 className="text-3xl font-black text-slate-900 mb-10 tracking-tight">{t('checkout.shipping_info')}</h1>

      
      <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <CheckoutForm 
          formData={formData} 
          onInputChange={handleInputChange}
          onPaymentChange={handlePaymentChange}
          onShippingChange={handleShippingChange}
          onBillingToggle={handleBillingToggle}
          isSubmitting={isSubmitting} 
        />
        
        <OrderSummary 
          items={items} 
          total={getCartTotal()} 
          shippingCost={shippingCost}
          isSubmitting={isSubmitting} 
        />
      </form>
    </div>
  );
};

export default CheckoutPage;


