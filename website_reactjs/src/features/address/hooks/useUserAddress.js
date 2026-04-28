import { useCallback } from 'react';
import useAuthStore from '../../auth/store/useAuthStore';
import addressService from '../services/addressService';
import axiosClient from '../../../api/axiosClient';

/**
 * Hook to manage user addresses with auto-sync to auth store
 * Used by: AddressBook, Checkout, Profile, etc.
 */
const useUserAddress = () => {
  const { user, refreshUser } = useAuthStore();

  // Get default addresses from user (from backend relationships)
  const defaultShipping = user?.default_shipping_address || user?.defaultShippingAddress;
  const defaultBilling = user?.default_billing_address || user?.defaultBillingAddress;

  /**
   * Pre-fill form data with default shipping address
   * @param {Object} baseData - Base form data to merge with
   * @returns {Object} Form data with address pre-filled
   */
  const getShippingFormData = useCallback((baseData = {}) => ({
    firstName: defaultShipping?.first_name || user?.first_name || baseData.firstName || '',
    lastName: defaultShipping?.last_name || user?.last_name || baseData.lastName || '',
    phone: defaultShipping?.phone || user?.phone || baseData.phone || '',
    email: user?.email || baseData.email || '',
    address: defaultShipping?.street || baseData.address || '',
    city: defaultShipping?.city || baseData.city || '',
    country: defaultShipping?.country_code || user?.country_code || user?.country || baseData.country || 'VN',
    state: defaultShipping?.state_id || user?.state_id || baseData.state || '',
    region: defaultShipping?.city_id || user?.city_id || baseData.region || '',
    subRegion: defaultShipping?.ward_id || user?.ward_id || baseData.subRegion || '',
  }), [defaultShipping, user]);

  /**
   * Pre-fill form data with default billing address
   * @param {Object} baseData - Base form data to merge with
   * @returns {Object} Form data with billing address pre-filled
   */
  const getBillingFormData = useCallback((baseData = {}) => ({
    billingFirstName: defaultBilling?.first_name || baseData.billingFirstName || '',
    billingLastName: defaultBilling?.last_name || baseData.billingLastName || '',
    billingPhone: defaultBilling?.phone || baseData.billingPhone || '',
    billingEmail: defaultBilling?.email || user?.email || baseData.billingEmail || '',
    billingAddress: defaultBilling?.street || baseData.billingAddress || '',
    billingCity: defaultBilling?.city || baseData.billingCity || '',
    billingCountry: defaultBilling?.country_code || user?.country_code || user?.country || baseData.billingCountry || 'VN',
    billingState: defaultBilling?.state_id || baseData.billingState || '',
    billingRegion: defaultBilling?.city_id || baseData.billingRegion || '',
    billingSubRegion: defaultBilling?.ward_id || baseData.billingSubRegion || '',
    billingSameAsShipping: !defaultBilling,
  }), [defaultBilling, user]);

  /**
   * Set an address as default (shipping or billing)
   * @param {string} addressId - Address ID to set as default
   * @param {string} type - 'shipping' or 'billing'
   * @returns {Promise<boolean>} Success status
   */
  const setDefaultAddress = useCallback(async (addressId, type) => {
    try {
      const payload = {};
      if (type === 'shipping') payload.default_shipping_address_id = addressId;
      if (type === 'billing') payload.default_billing_address_id = addressId;

      await axiosClient.put('user/profile', payload);

      // Refresh user to sync default addresses
      await refreshUser();

      return true;
    } catch (error) {
      console.error('Failed to set default address:', error);
      return false;
    }
  }, [refreshUser]);

  /**
   * Fetch all user addresses
   * @returns {Promise<Array>} List of addresses
   */
  const fetchUserAddresses = useCallback(async () => {
    try {
      const response = await addressService.listUserAddresses();
      return response.data || [];
    } catch (error) {
      console.error('Failed to fetch addresses:', error);
      return [];
    }
  }, []);

  /**
   * Create new address
   * @param {Object} data - Address data
   * @returns {Promise<Object|null>} Created address or null
   */
  const createAddress = useCallback(async (data) => {
    try {
      const response = await addressService.createUserAddress(data);
      return response.data;
    } catch (error) {
      console.error('Failed to create address:', error);
      return null;
    }
  }, []);

  /**
   * Update existing address
   * @param {string} id - Address ID
   * @param {Object} data - Address data
   * @returns {Promise<Object|null>} Updated address or null
   */
  const updateAddress = useCallback(async (id, data) => {
    try {
      const response = await addressService.updateUserAddress(id, data);
      return response.data;
    } catch (error) {
      console.error('Failed to update address:', error);
      return null;
    }
  }, []);

  /**
   * Delete address
   * @param {string} id - Address ID
   * @returns {Promise<boolean>} Success status
   */
  const deleteAddress = useCallback(async (id) => {
    try {
      await addressService.deleteUserAddress(id);
      return true;
    } catch (error) {
      console.error('Failed to delete address:', error);
      return false;
    }
  }, []);

  return {
    // Data
    user,
    defaultShipping,
    defaultBilling,

    // Form helpers
    getShippingFormData,
    getBillingFormData,

    // CRUD operations
    fetchUserAddresses,
    createAddress,
    updateAddress,
    deleteAddress,
    setDefaultAddress,

    // Sync
    refreshUser,
  };
};

export default useUserAddress;
