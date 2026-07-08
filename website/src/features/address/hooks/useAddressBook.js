import { useState, useEffect, useCallback } from 'react';
import addressService from '../services/addressService';

/**
 * Custom hook for AddressBook logic
 * Manages address CRUD and location data fetching
 */
const useAddressBook = () => {
  // Addresses list
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [editingAddress, setEditingAddress] = useState(null);

  // Form state
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    type: 'shipping',
    country_code: 'VN',
    state_id: '',
    city_id: '',
    ward_id: '',
    street: '',
    company: '',
    zip: '',
  });

  // Location data
  const [countries, setCountries] = useState([]);
  const [states, setStates] = useState([]);
  const [cities, setCities] = useState([]);
  const [wards, setWards] = useState([]);

  // Fetch user addresses
  const fetchAddresses = useCallback(async () => {
    try {
      setLoading(true);
      const response = await addressService.listUserAddresses();
      setAddresses(response.data || []);
    } catch (error) {
      console.error('Failed to fetch addresses:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  // Fetch countries on mount
  const fetchCountries = useCallback(async () => {
    try {
      const response = await addressService.getCountries();
      const entries = Object.entries(response.data || {}).map(([code, name]) => ({ code, name }));
      setCountries(entries);
    } catch (error) {
      console.error('Failed to fetch countries:', error);
    }
  }, []);

  // Fetch states when country changes
  const fetchStates = useCallback(async (code) => {
    if (!code) return;
    try {
      const response = await addressService.getStates(code);
      const entries = Object.entries(response.data || {}).map(([id, name]) => ({ id, name }));
      setStates(entries);
    } catch (error) {
      console.error('Failed to fetch states:', error);
    }
  }, []);

  // Fetch cities/regions when state changes
  const fetchCities = useCallback(async (country, state) => {
    if (!country || !state) return;
    try {
      const response = await addressService.getRegions(country, state);
      const entries = Object.entries(response.data || {}).map(([id, name]) => ({ id, name }));
      setCities(entries);
    } catch (error) {
      console.error('Failed to fetch cities:', error);
    }
  }, []);

  // Fetch wards/sub-regions when city changes
  const fetchWards = useCallback(async (country, state, city) => {
    if (!country || !state || !city) return;
    try {
      const response = await addressService.getSubRegions(country, state, city);
      const entries = Object.entries(response.data || {}).map(([id, name]) => ({ id, name }));
      setWards(entries);
    } catch (error) {
      console.error('Failed to fetch wards:', error);
    }
  }, []);

  // Reset form
  const resetForm = useCallback(() => {
    setFormData({
      first_name: '',
      last_name: '',
      phone: '',
      email: '',
      type: 'shipping',
      country_code: 'VN',
      state_id: '',
      city_id: '',
      ward_id: '',
      street: '',
      company: '',
      zip: '',
    });
    setEditingAddress(null);
    setShowForm(false);
  }, []);

  // Set form for editing
  const handleEdit = useCallback((addr) => {
    setEditingAddress(addr);
    setFormData({
      first_name: addr.first_name || '',
      last_name: addr.last_name || '',
      phone: addr.phone || '',
      email: addr.email || '',
      type: addr.type || 'shipping',
      country_code: addr.country_code || 'VN',
      state_id: addr.state_id || '',
      city_id: addr.city_id || '',
      ward_id: addr.ward_id || '',
      street: addr.street || '',
      company: addr.company || '',
      zip: addr.postal_code || '',
    });
    setShowForm(true);
  }, []);

  // Create or update address
  const handleSubmit = useCallback(async (e) => {
    e.preventDefault();
    try {
      if (editingAddress) {
        await addressService.updateUserAddress(editingAddress.id, formData);
      } else {
        await addressService.createUserAddress(formData);
      }
      resetForm();
      fetchAddresses();
      return true;
    } catch (error) {
      console.error('Failed to save address:', error);
      return false;
    }
  }, [editingAddress, formData, resetForm, fetchAddresses]);

  // Delete address
  const handleDelete = useCallback(async (id) => {
    try {
      await addressService.deleteUserAddress(id);
      fetchAddresses();
      return true;
    } catch (error) {
      console.error('Failed to delete address:', error);
      return false;
    }
  }, [fetchAddresses]);

  // Effects for cascading location dropdowns
  useEffect(() => {
    fetchAddresses();
    fetchCountries();
  }, [fetchAddresses, fetchCountries]);

  useEffect(() => {
    if (formData.country_code) {
      fetchStates(formData.country_code);
    }
  }, [formData.country_code, fetchStates]);

  useEffect(() => {
    if (formData.country_code && formData.state_id) {
      fetchCities(formData.country_code, formData.state_id);
    }
  }, [formData.country_code, formData.state_id, fetchCities]);

  useEffect(() => {
    if (formData.country_code && formData.state_id && formData.city_id) {
      fetchWards(formData.country_code, formData.state_id, formData.city_id);
    }
  }, [formData.country_code, formData.state_id, formData.city_id, fetchWards]);

  return {
    // State
    addresses,
    loading,
    showForm,
    editingAddress,
    formData,
    setFormData,
    countries,
    states,
    cities,
    wards,

    // Actions
    setShowForm,
    resetForm,
    handleEdit,
    handleSubmit,
    handleDelete,
    fetchAddresses,
  };
};

export default useAddressBook;
