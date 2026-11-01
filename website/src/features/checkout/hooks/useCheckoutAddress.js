import { useState, useEffect } from 'react';
import useSettingsStore from '../../../store/useSettingsStore';
import useCartStore from '../../cart/store/useCartStore';
import useAddressSelect from '../../address/hooks/useAddressSelect';
import cartApi from '../../cart/services/cartApi';
import { unwrapApiList } from '../../../api/apiResponse';

/**
 * Custom hook for checkout address & shipping/payment method logic
 *
 * @param {object} formData - Current form data
 * @returns {object} Address data and available payment/shipping methods
 */
const useCheckoutAddress = (formData) => {
  const getSetting = useSettingsStore((state) => state.getSetting);
  const translate = useSettingsStore((state) => state.translate);
  const language = useSettingsStore((state) => state.language);
  const items = useCartStore((state) => state.items);

  const configuredPaymentMethods = getSetting('checkout.payment_gateways');
  const paymentMethods =
    Array.isArray(configuredPaymentMethods) && configuredPaymentMethods.length > 0
      ? configuredPaymentMethods
      : [
          {
            id: 'cod',
            name: translate('checkout.cod') || 'Cash on Delivery (COD)',
            icon: 'truck',
          },
          {
            id: 'bank_transfer',
            name: translate('checkout.bank_transfer') || 'Bank Transfer',
            icon: 'credit-card',
          },
        ];

  const configuredShippingMethods = getSetting('checkout.shipping_methods') || [];
  const [dynamicShippingMethods, setDynamicShippingMethods] = useState([]);

  const {
    countries,
    states,
    regions,
    subRegions,
    fetchStates,
    fetchRegions,
    fetchSubRegions,
  } = useAddressSelect();

  useEffect(() => {
    if (formData?.country) {
      fetchStates(formData.country);
    }
  }, [formData?.country, fetchStates]);

  useEffect(() => {
    if (formData?.country && formData?.state) {
      fetchRegions(formData.country, formData.state);
    }
  }, [formData?.country, formData?.state, fetchRegions]);

  useEffect(() => {
    if (formData?.country && formData?.state && formData?.region) {
      fetchSubRegions(formData.country, formData.state, formData.region);
    }
  }, [formData?.country, formData?.state, formData?.region, fetchSubRegions]);

  const itemsCount = (items || []).reduce((acc, i) => acc + (i.quantity || 1), 0);

  // Fetch dynamic shipping methods from backend when cart items or address changes
  useEffect(() => {
    let isMounted = true;
    const loadShippingMethods = async () => {
      if (!items || items.length === 0) return;
      try {
        const res = await cartApi.getShippingMethods(
          items,
          formData?.country || 'VN',
          formData?.state || null
        );
        const list = unwrapApiList(res, []);
        if (isMounted && Array.isArray(list) && list.length > 0) {
          setDynamicShippingMethods(list);
        }
      } catch (e) {
        // Fallback to settings shipping methods if endpoint fails
      }
    };
    loadShippingMethods();
    return () => {
      isMounted = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [itemsCount, formData?.country, formData?.state, language]);

  const shippingMethods =
    dynamicShippingMethods.length > 0 ? dynamicShippingMethods : configuredShippingMethods;

  return {
    countries,
    states,
    regions,
    subRegions,
    paymentMethods,
    shippingMethods,
  };
};

export default useCheckoutAddress;
