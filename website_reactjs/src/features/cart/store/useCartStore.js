import { create } from 'zustand';
import { persist } from 'zustand/middleware';

const useCartStore = create(
  persist(
    (set, get) => ({
      items: [],

      addToCart: (product, quantity = 1) => {
        const currentItems = get().items;
        const existingItem = currentItems.find(item => item.id === product.id);

        if (existingItem) {
          set({
            items: currentItems.map(item =>
              item.id === product.id
                ? { ...item, quantity: item.quantity + quantity }
                : item
            )
          });
        } else {
          set({ items: [...currentItems, { ...product, quantity }] });
        }
      },

      removeFromCart: (productId) => {
        set({ items: get().items.filter(item => item.id !== productId) });
      },

      updateQuantity: (productId, quantity) => {
        if (quantity <= 0) {
          get().removeFromCart(productId);
          return;
        }
        set({
          items: get().items.map(item =>
            item.id === productId ? { ...item, quantity } : item
          )
        });
      },

      clearCart: () => set({ items: [] }),

      getCartTotal: () => {
        return get().items.reduce((total, item) => total + (item.price * item.quantity), 0);
      },

      getCartCount: () => {
        return get().items.reduce((count, item) => count + item.quantity, 0);
      },

      mergeCart: async (remoteItems) => {
        // Simple merge logic: if item exists in both, keep the one with higher quantity or combine
        // For now, just combining if remoteItems provided
        if (!remoteItems) return;

        const localItems = get().items;
        const merged = [...localItems];

        remoteItems.forEach(remoteItem => {
          const index = merged.findIndex(item => item.id === remoteItem.id);
          if (index > -1) {
            merged[index].quantity = Math.max(merged[index].quantity, remoteItem.quantity);
          } else {
            merged.push(remoteItem);
          }
        });

        set({ items: merged });
      }
    }),
    {
      name: 'ecommerce-cart-storage',
    }
  )
);

export default useCartStore;
