import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import useSettingsStore from '../../../store/useSettingsStore';

const useCartStore = create(
  persist(
    (set, get) => ({
      items: [],
      itemsById: {},
      summary: null, // { subtotal, shipping, tax, total, items_count }
      notifications: [], // API notifications (stock_adjusted, price_change, etc.)
      isCartOpen: false,

      toggleCart: () => set((state) => ({ isCartOpen: !state.isCartOpen })),
      closeCart: () => set({ isCartOpen: false }),
      openCart: () => set({ isCartOpen: true }),

      addToCart: async (product, quantity = 1) => {
        const currentById = get().itemsById || {};
        const variantId = product.variant_id || product.variant?.id || null;
        const cartKey = variantId ? `${product.id}-v${variantId}` : `${product.id}`;
        const existingItem = currentById[cartKey];

        let updatedById;
        if (existingItem) {
          updatedById = {
            ...currentById,
            [cartKey]: { ...existingItem, quantity: existingItem.quantity + quantity }
          };
        } else {
          updatedById = {
            ...currentById,
            [cartKey]: {
              ...product,
              cart_key: cartKey,
              variant_id: variantId,
              quantity,
              selectedWarehouse: product.selectedWarehouse || null
            }
          };
        }

        set({
          itemsById: updatedById,
          items: Object.values(updatedById)
        });

        await get().syncWithBackend(Object.values(updatedById));
      },

      removeFromCart: async (cartKey) => {
        const currentById = { ...get().itemsById };
        delete currentById[cartKey];
        const newItems = Object.values(currentById);

        set({
          itemsById: currentById,
          items: newItems
        });

        await get().syncWithBackend(newItems);
      },

      updateQuantity: async (cartKey, quantity) => {
        if (quantity <= 0) {
          get().removeFromCart(cartKey);
          return;
        }

        const currentById = { ...get().itemsById };
        if (currentById[cartKey]) {
          currentById[cartKey] = { ...currentById[cartKey], quantity };
        }

        const newItems = Object.values(currentById);

        set({
          itemsById: currentById,
          items: newItems
        });

        await get().syncWithBackend(newItems);
      },

      clearCart: async () => {
        set({ items: [], itemsById: {}, summary: null, notifications: [] });
        await get().syncWithBackend([]);
      },

      removeCoupon: async () => {
        const currentSummary = get().summary;
        if (currentSummary) {
          set({
            summary: {
              ...currentSummary,
              coupon: null,
              coupon_code: null,
              discount: { amount: 0 },
              discount_amount: 0,
            },
          });
        }
        await get().syncWithBackend(get().items);
      },

      syncWithBackend: async (items) => {
        try {
          const { default: cartApi } = await import('../services/cartApi');
          const payload = (items || []).map(item => ({
            product_id: item.product_id || item.id,
            quantity: item.quantity,
            variant_id: item.variant_id || item.variant?.id || null,
            price: item.price,
          }));

          const response = await cartApi.syncCart({ items: payload });
          const data = response?.data || response;
          if (data) {
            set({ summary: data.summary || data });
          }
        } catch (error) {
          console.error("Cart sync failed", error);
          const { default: toast } = await import('react-hot-toast');
          const msg =
            useSettingsStore?.getState?.()?.translate?.('errors.cart_sync_failed') ||
            "Không thể đồng bộ giỏ hàng với máy chủ.";
          toast.error(msg);
        }
      },

      // Set full cart data from API (items + summary + notifications)
      setCart: (data) => {
        if (!data) return;
        set({
          items: data.items || get().items,
          itemsById: data.items ? Object.fromEntries(data.items.map(i => [i.id, i])) : get().itemsById,
          summary: data.summary || data || get().summary,
          notifications: data.notifications || [],
        });
      },

      getCartTotal: () => {
        return get().summary?.total ?? 0;
      },

      getCartCount: () => {
        return Object.values(get().itemsById).reduce((count, item) => count + item.quantity, 0);
      },

      mergeCart: async (remoteItems) => {
        if (!remoteItems) return;

        const currentById = { ...get().itemsById };

        remoteItems.forEach(remoteItem => {
          if (currentById[remoteItem.id]) {
            currentById[remoteItem.id].quantity = Math.max(currentById[remoteItem.id].quantity, remoteItem.quantity);
          } else {
            currentById[remoteItem.id] = remoteItem;
          }
        });

        set({
          itemsById: currentById,
          items: Object.values(currentById)
        });
      },

      getItemNotifications: (itemId) => {
        return get().notifications.filter(n => n.product_id === itemId);
      },

      clearNotifications: () => set({ notifications: [] }),
    }),
    {
      name: 'ecommerce-cart-storage',
    }
  )
);

export default useCartStore;
