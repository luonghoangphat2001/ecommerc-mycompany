import { create } from 'zustand';
import { persist } from 'zustand/middleware';

const useCartStore = create(
  persist(
    (set, get) => ({
      items: [],
      itemsById: {},

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
            [product.id]: { ...product, quantity }
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

      clearCart: () => set({ items: [], itemsById: {} }),

      getCartTotal: () => {
        // O(1) derived access
        return Object.values(get().itemsById).reduce((total, item) => total + (item.price * item.quantity), 0);
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
      }
    }),
    {
      name: 'ecommerce-cart-storage',
    }
  )
);

export default useCartStore;
