import { useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import checkoutApi from '../services/checkoutApi';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';

const checkoutSchema = z.object({
  shipping_address: z.object({
    first_name: z.string().min(1, 'Required'),
    last_name: z.string().min(1, 'Required'),
    email: z.string().email('Invalid email'),
    phone: z.string().min(10, 'Invalid phone'),
    address: z.string().min(1, 'Required'),
    city: z.string().min(1, 'Required'),
    postal_code: z.string().min(1, 'Required'),
    country: z.string().min(1, 'Required'),
  }),
  shipping_method_id: z.string().min(1, 'Required'),
  payment_method: z.string().min(1, 'Required'),
  notes: z.string().optional(),
});

export const useCheckout = () => {
  const [selectedShippingMethod, setSelectedShippingMethod] = useState(null);
  const [selectedAddress, setSelectedAddress] = useState(null);

  const form = useForm({
    resolver: zodResolver(checkoutSchema),
    defaultValues: {
      shipping_address: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        postal_code: '',
        country: 'Vietnam',
      },
      shipping_method_id: '',
      payment_method: 'cod',
      notes: '',
    },
  });

  const { data: shippingMethods, isLoading: loadingShipping } = useQuery({
    queryKey: ['shipping-methods'],
    queryFn: () => checkoutApi.getShippingMethods(),
  });

  const createOrderMutation = useMutation({
    mutationFn: (orderData) => checkoutApi.createOrder(orderData),
    onSuccess: (data) => {
      return data.data;
    },
  });

  const validateAddressMutation = useMutation({
    mutationFn: (addressData) => checkoutApi.validateShippingAddress(addressData),
  });

  const calculateShippingMutation = useMutation({
    mutationFn: ({ addressId, shippingMethodId }) => 
      checkoutApi.calculateShipping(addressId, shippingMethodId),
  });

  const handleCreateOrder = async (data) => {
    return createOrderMutation.mutateAsync(data);
  };

  const handleValidateAddress = async (addressData) => {
    return validateAddressMutation.mutateAsync(addressData);
  };

  const handleCalculateShipping = async (addressId, shippingMethodId) => {
    return calculateShippingMutation.mutateAsync({ addressId, shippingMethodId });
  };

  return {
    form,
    shippingMethods: shippingMethods?.data || [],
    loadingShipping,
    selectedShippingMethod,
    setSelectedShippingMethod,
    selectedAddress,
    setSelectedAddress,
    createOrder: handleCreateOrder,
    validateAddress: handleValidateAddress,
    calculateShipping: handleCalculateShipping,
    isCreatingOrder: createOrderMutation.isLoading,
    isValidatingAddress: validateAddressMutation.isLoading,
    isCalculatingShipping: calculateShippingMutation.isLoading,
  };
};
