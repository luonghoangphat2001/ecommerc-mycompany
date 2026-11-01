import { useMutation, useQueryClient } from '@tanstack/react-query';
import cartApi from '../services/cartApi';
import useCartStore from '../store/useCartStore';

export const useCartActions = () => {
  const queryClient = useQueryClient();
  const { items, setCart, clearCart: clearLocalCart, removeCoupon: removeLocalCoupon } = useCartStore();

  const addToCartMutation = useMutation({
    mutationFn: ({ productId, variantId, quantity }) =>
      cartApi.addToCart(productId, quantity, variantId),
    onSuccess: (data) => {
      setCart(data?.data || data);
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });

  const updateCartItemMutation = useMutation({
    mutationFn: ({ itemId, quantity }) =>
      cartApi.updateCartItem(itemId, quantity),
    onSuccess: (data) => {
      setCart(data?.data || data);
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });

  const removeCartItemMutation = useMutation({
    mutationFn: (itemId) => cartApi.removeCartItem(itemId),
    onSuccess: (data) => {
      setCart(data?.data || data);
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });

  const syncCartMutation = useMutation({
    mutationFn: (cartItems) => {
      const payload = Array.isArray(cartItems) ? { items: cartItems } : cartItems;
      return cartApi.syncCart(payload);
    },
    onSuccess: (data) => {
      setCart(data?.data || data);
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });

  const clearCartMutation = useMutation({
    mutationFn: () => cartApi.clearCart(),
    onSuccess: () => {
      clearLocalCart();
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });

  const applyCouponMutation = useMutation({
    mutationFn: (couponCode) => cartApi.applyCoupon(couponCode, items),
    onSuccess: (data) => {
      setCart(data?.data || data);
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });

  const removeCouponMutation = useMutation({
    mutationFn: async () => {
      await removeLocalCoupon();
      return cartApi.removeCoupon(items);
    },
    onSuccess: (data) => {
      setCart(data?.data || data);
      queryClient.invalidateQueries({ queryKey: ['cart'] });
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
    isLoading:
      addToCartMutation.isPending ||
      updateCartItemMutation.isPending ||
      removeCartItemMutation.isPending,
  };
};
