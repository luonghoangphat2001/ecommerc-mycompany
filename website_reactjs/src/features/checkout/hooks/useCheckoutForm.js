import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useCartStore from '../../cart/store/useCartStore';
import orderService from '../../order/services/orderService';

const useCheckoutForm = (initialUser, onCheckoutSuccess) => {
  const navigate = useNavigate();
  const { items, clearCart, getCartTotal } = useCartStore();
  
  const [formData, setFormData] = useState({
    firstName: initialUser?.first_name || '',
    lastName: initialUser?.last_name || '',
    email: initialUser?.email || '',
    phone: initialUser?.phone || '',
    address: '',
    city: '',
    note: ''
  });

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (items.length === 0) {
      setError('Giỏ hàng của bạn đang trống');
      return;
    }

    setIsSubmitting(true);
    setError(null);
    
    try {
      const orderData = {
        email: formData.email,
        phone: formData.phone,
        shipping_address: {
          first_name: formData.firstName,
          last_name: formData.lastName,
          phone: formData.phone,
          country: 'VN', // Default or from settings
          street: formData.address,
          city: formData.city,
        },
        items: items.map(item => ({
          product_id: item.id,
          qty: item.quantity
        })),
        shipping_method: 'flat_rate', // Default
        payment_method: 'cod', // Default
        notes: formData.note
      };

      const response = await orderService.create(orderData);
      
      clearCart();
      if (onCheckoutSuccess) onCheckoutSuccess(response.data);
      navigate('/checkout/success', { state: { order: response.data } });
    } catch (err) {
      console.error('Checkout error:', err);
      setError(err.response?.data?.message || 'Có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  return {
    formData,
    isSubmitting,
    error,
    handleSubmit,
    handleInputChange
  };
};

export default useCheckoutForm;
