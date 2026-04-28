import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import useCartStore from '../../cart/store/useCartStore';
import useSettingsStore from '../../../store/useSettingsStore';
import useUserAddress from '../../address/hooks/useUserAddress';
import orderService from '../../order/services/orderService';

const useCheckoutForm = (initialUser, onCheckoutSuccess) => {
  const navigate = useNavigate();
  const { items, clearCart, getCartTotal } = useCartStore();
  const translate = useSettingsStore((state) => state.translate);
  const { getShippingFormData, getBillingFormData } = useUserAddress();

  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: 'VN',
    state: '',
    region: '',
    subRegion: '',
    note: '',
    paymentMethod: 'cod',
    shippingMethod: '',
    billingSameAsShipping: true,
    billingFirstName: '',
    billingLastName: '',
    billingPhone: '',
    billingEmail: '',
    billingAddress: '',
    billingCity: '',
    billingCountry: 'VN',
    billingState: '',
    billingRegion: '',
    billingSubRegion: ''
  });

  // Update form when user data changes (after API load)
  useEffect(() => {
    if (initialUser) {
      const shipping = getShippingFormData();
      const billing = getBillingFormData();
      
      setFormData(prev => ({
        ...prev,
        firstName: shipping.firstName || prev.firstName,
        lastName: shipping.lastName || prev.lastName,
        email: shipping.email || prev.email,
        phone: shipping.phone || prev.phone,
        address: shipping.address || prev.address,
        city: shipping.city || prev.city,
        country: shipping.country || prev.country,
        state: shipping.state || prev.state,
        region: shipping.region || prev.region,
        subRegion: shipping.subRegion || prev.subRegion,
        billingFirstName: billing.billingFirstName || prev.billingFirstName,
        billingLastName: billing.billingLastName || prev.billingLastName,
        billingPhone: billing.billingPhone || prev.billingPhone,
        billingEmail: billing.billingEmail || prev.billingEmail,
        billingAddress: billing.billingAddress || prev.billingAddress,
        billingCity: billing.billingCity || prev.billingCity,
        billingCountry: billing.billingCountry || prev.billingCountry,
        billingState: billing.billingState || prev.billingState,
        billingRegion: billing.billingRegion || prev.billingRegion,
        billingSubRegion: billing.billingSubRegion || prev.billingSubRegion,
        billingSameAsShipping: billing.billingSameAsShipping
      }));
    }
  }, [initialUser, getShippingFormData, getBillingFormData]);

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (items.length === 0) {
      setError(translate('checkout.cart_empty_error'));
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
          country: formData.country || 'VN',
          street: formData.address,
          city: formData.city,
          state: formData.state,
          region: formData.region,
          sub_region: formData.subRegion,
        },
        billing_address: formData.billingSameAsShipping ? undefined : {
          first_name: formData.billingFirstName,
          last_name: formData.billingLastName,
          phone: formData.billingPhone,
          email: formData.billingEmail,
          country: formData.billingCountry || 'VN',
          street: formData.billingAddress,
          city: formData.billingCity,
          state: formData.billingState,
          region: formData.billingRegion,
          sub_region: formData.billingSubRegion,
        },
        items: items.map(item => ({
          product_id: item.id,
          qty: item.quantity,
          price: item.price // 🛡️ Send price for Backend Integrity Check
        })),
        shipping_method: formData.shippingMethod || 'flat_rate',
        payment_method: formData.paymentMethod || 'cod',
        notes: formData.note
      };

      const response = await orderService.create(orderData);
      const order = response.data;
      
      clearCart();
      // Redirect to professional success page with order data
      navigate('/checkout/success', { state: { order } });
    } catch (err) {
      console.error('Checkout error:', err);
      setError(err.response?.data?.message || translate('checkout.generic_error'));
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value, type } = e.target;
    // Handle number inputs properly
    if (type === 'number') {
      setFormData(prev => ({ ...prev, [name]: value === '' ? '' : parseFloat(value) }));
    } else {
      setFormData(prev => ({ ...prev, [name]: value }));
    }
  };

  const handlePaymentChange = (e) => {
    setFormData(prev => ({ ...prev, paymentMethod: e.target.value }));
  };

  const handleShippingChange = (e) => {
    setFormData(prev => ({ ...prev, shippingMethod: e.target.value }));
  };

  const handleBillingToggle = (e) => {
    const checked = e.target.checked;
    setFormData(prev => {
      const newState = { ...prev, billingSameAsShipping: checked };
      // Auto-fill billing with shipping if checked
      if (checked) {
        newState.billingFirstName = prev.firstName;
        newState.billingLastName = prev.lastName;
        newState.billingPhone = prev.phone;
        newState.billingEmail = prev.email;
        newState.billingAddress = prev.address;
        newState.billingCity = prev.city;
        newState.billingCountry = prev.country;
        newState.billingState = prev.state;
        newState.billingRegion = prev.region;
        newState.billingSubRegion = prev.subRegion;
      }
      return newState;
    });
  };

  return {
    formData,
    isSubmitting,
    error,
    handleSubmit,
    handleInputChange,
    handlePaymentChange,
    handleShippingChange,
    handleBillingToggle
  };
};

export default useCheckoutForm;
