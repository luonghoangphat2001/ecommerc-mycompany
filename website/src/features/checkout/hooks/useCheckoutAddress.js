import { useEffect } from 'react';
import useSettingsStore from '../../../store/useSettingsStore';
import useAddressSelect from '../../address/hooks/useAddressSelect';

/**
 * Custom hook for checkout address logic
 * Separates business logic from UI component
 * 
 * @param {object} formData - Current form data
 * @returns {object} Address data and loading states
 */
const useCheckoutAddress = (formData) => {
  const getSetting = useSettingsStore(state => state.getSetting);
  
  // Settings
  const paymentMethods = getSetting('checkout.payment_gateways') || [
    { id: 'cod', name: 'Thanh toán khi nhận hàng (COD)', icon: 'truck' },
    { id: 'bank_transfer', name: 'Chuyển khoản ngân hàng', icon: 'credit-card' }
  ];
  const shippingMethods = getSetting('checkout.shipping_methods') || [];
  
  // Address selection logic from API
  const { 
    countries, states, regions, subRegions, 
    fetchStates, fetchRegions, fetchSubRegions 
  } = useAddressSelect();

  // Fetch states when country changes
  useEffect(() => {
    if (formData?.country) {
      fetchStates(formData.country);
    }
  }, [formData?.country, fetchStates]);

  // Fetch regions when state changes
  useEffect(() => {
    if (formData?.country && formData?.state) {
      fetchRegions(formData.country, formData.state);
    }
  }, [formData?.country, formData?.state, fetchRegions]);

  // Fetch sub-regions when region changes
  useEffect(() => {
    if (formData?.country && formData?.state && formData?.region) {
      fetchSubRegions(formData.country, formData.state, formData.region);
    }
  }, [formData?.country, formData?.state, formData?.region, fetchSubRegions]);

  return {
    countries,
    states,
    regions,
    subRegions,
    paymentMethods,
    shippingMethods
  };
};

export default useCheckoutAddress;
