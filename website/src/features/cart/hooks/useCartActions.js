import { useMutation, useQueryClient } from '@tanstack/react-query';
import cartApi from '../services/cartApi';
import useCartStore from '../store/useCartStore';

export const useCartActions = () => {
  const queryClient = useQueryClient();
  const { setCart, clearCart: clearLocalCart } = useCartStore();

  const addToCartMutation = useMutation({
    mutationFn: ({ productId, variantId, quantity }) =>
      cartApi.addToCart(productId, variantId, quantity),
    onSuccess: (data) => {
      setCart(data.data);
      queryClient.invalidateQueries(['cart']);
    },
  });

  const updateCartItemMutation = useMutation({
    mutationFn: ({ itemId, quantity }) =>
      cartApi.updateCartItem(itemId, quantity),
    onSuccess: (data) => {
      setCart(data.data);
      queryClient.invalidateQueries(['cart']);
    },
  });

  const removeCartItemMutation = useMutation({
    mutationFn: (itemId) => cartApi.removeCartItem(itemId),
    onSuccess: (data) => {
      setCart(data.data);
      queryClient.invalidateQueries(['cart']);
    },
  });

  const syncCartMutation = useMutation({
    mutationFn: (cartItems) => cartApi.syncCart(cartItems),
    onSuccess: (data) => {
      setCart(data.data);
      queryClient.invalidateQueries(['cart']);
    },
  });

  const clearCartMutation = useMutation({
    mutationFn: () => cartApi.clearCart(),
    onSuccess: () => {
      clearLocalCart();
      queryClient.invalidateQueries(['cart']);
    },
  });

  const applyCouponMutation = useMutation({
    mutationFn: (couponCode) => cartApi.applyCoupon(couponCode),
    onSuccess: (data) => {
      setCart(data.data);
      queryClient.invalidateQueries(['cart']);
    },
  });

  const removeCouponMutation = useMutation({
    mutationFn: () => cartApi.removeCoupon(),
    onSuccess: (data) => {
      setCart(data.data);
      queryClient.invalidateQueries(['cart']);
    },
  });

  return {
    addToCart: addToCartMutation.mutate,
    updateCartItem: updateCartItemMutation.mutate,
    removeCartItem: removeCartItemMutation.mutate,
    syncCart: syncCartMutation.mutate,
    clearCart: clearCartMutation.mutate,
    applyCoupon: applyCouponMutation.mutate,
    removeCoupon: removeCouponMutation.mutate,
    isLoading: addToCartMutation.isLoading ||
      updateCartItemMutation.isLoading ||
      removeCartItemMutation.isLoading,
  };
};
