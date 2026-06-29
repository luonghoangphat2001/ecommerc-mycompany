import { create } from 'zustand';
import { persist } from 'zustand/middleware';

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

        addToCart: (product, quantity = 1) => {
            const currentById = get().itemsById || {};
            const existingItem = currentById[product.id];
            
            let updatedById;
            if (existingItem) {
                updatedById = {
                    ...currentById,
                    [product.id]: { ...existingItem, quantity: existingItem.quantity + quantity }
                };
            } else {
                updatedById = {
                    ...currentById,
                    [product.id]: { 
                        ...product, 
                        quantity,
                        selectedWarehouse: product.selectedWarehouse || null
                    }
                };
            }
            
            set({
                itemsById: updatedById,
                items: Object.values(updatedById)
            });
        },

      removeFromCart: (productId) => {
        const currentById = { ...get().itemsById };
        delete currentById[productId];

        set({
          itemsById: currentById,
          items: Object.values(currentById)
        });
      },

      updateQuantity: (productId, quantity) => {
        if (quantity <= 0) {
          get().removeFromCart(productId);
          return;
        }

        const currentById = { ...get().itemsById };
        if (currentById[productId]) {
          currentById[productId] = { ...currentById[productId], quantity };
        }

        set({
          itemsById: currentById,
          items: Object.values(currentById)
        });
      },

      clearCart: () => set({ items: [], itemsById: {}, summary: null, notifications: [] }),

      // Set full cart data from API (items + summary + notifications)
      setCart: (data) => set({
        items: data.items || get().items,
        itemsById: data.items ? Object.fromEntries(data.items.map(i => [i.id, i])) : get().itemsById,
        summary: data.summary || get().summary,
        notifications: data.notifications || [],
      }),

      getCartTotal: () => {
        // Use summary total if available, otherwise calculate locally
        return get().summary?.total || Object.values(get().itemsById).reduce((total, item) => total + (item.price * item.quantity), 0);
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

      // Get notifications for a specific item
      getItemNotifications: (itemId) => {
        return get().notifications.filter(n => n.product_id === itemId);
      },

      // Clear notifications
      clearNotifications: () => set({ notifications: [] }),
    }),
    {
      name: 'ecommerce-cart-storage',
    }
  )
);

export default useCartStore;
