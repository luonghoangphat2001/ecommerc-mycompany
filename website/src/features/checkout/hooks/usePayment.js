import { useMutation, useQuery } from '@tanstack/react-query';
import paymentApi from '../services/paymentApi';

export const usePayment = () => {
  const { data: paymentMethods, isLoading: loadingMethods } = useQuery({
    queryKey: ['payment-methods'],
    queryFn: () => paymentApi.getPaymentMethods(),
  });

  const processPaymentMutation = useMutation({
    mutationFn: ({ orderId, paymentData }) => 
      paymentApi.processPayment(orderId, paymentData),
  });

  const processMomoMutation = useMutation({
    mutationFn: (orderId) => paymentApi.processMomoPayment(orderId),
  });

  const processVNPayMutation = useMutation({
    mutationFn: (orderId) => paymentApi.processVNPayPayment(orderId),
  });

  const processCODMutation = useMutation({
    mutationFn: (orderId) => paymentApi.processCODPayment(orderId),
  });

  const verifyPaymentMutation = useMutation({
    mutationFn: ({ paymentId, transactionId }) => 
      paymentApi.verifyPayment(paymentId, transactionId),
  });

  const getPaymentStatusMutation = useMutation({
    mutationFn: (orderId) => paymentApi.getPaymentStatus(orderId),
  });

  const handleProcessPayment = async (orderId, paymentData) => {
    return processPaymentMutation.mutateAsync({ orderId, paymentData });
  };

  const handleProcessMomo = async (orderId) => {
    return processMomoMutation.mutateAsync(orderId);
  };

  const handleProcessVNPay = async (orderId) => {
    return processVNPayMutation.mutateAsync(orderId);
  };

  const handleProcessCOD = async (orderId) => {
    return processCODMutation.mutateAsync(orderId);
  };

  const handleVerifyPayment = async (paymentId, transactionId) => {
    return verifyPaymentMutation.mutateAsync({ paymentId, transactionId });
  };

  const handleGetPaymentStatus = async (orderId) => {
    return getPaymentStatusMutation.mutateAsync(orderId);
  };

  return {
    paymentMethods: paymentMethods?.data || [],
    loadingMethods,
    processPayment: handleProcessPayment,
    processMomo: handleProcessMomo,
    processVNPay: handleProcessVNPay,
    processCOD: handleProcessCOD,
    verifyPayment: handleVerifyPayment,
    getPaymentStatus: handleGetPaymentStatus,
    isProcessing: processPaymentMutation.isLoading,
    isVerifying: verifyPaymentMutation.isLoading,
  };
};
