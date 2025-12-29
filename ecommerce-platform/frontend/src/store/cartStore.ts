import { create } from 'zustand';
import { CartItem } from '../types';
import api from '../lib/api';

interface CartState {
  items: CartItem[];
  loading: boolean;
  fetchCart: () => Promise<void>;
  addToCart: (productId: string, quantity: number) => Promise<void>;
  updateQuantity: (id: string, quantity: number) => Promise<void>;
  removeFromCart: (id: string) => Promise<void>;
  clearCart: () => Promise<void>;
  getTotal: () => number;
}

export const useCartStore = create<CartState>((set, get) => ({
  items: [],
  loading: false,

  fetchCart: async () => {
    set({ loading: true });
    try {
      const response = await api.get('/cart');
      set({ items: response.data.cartItems, loading: false });
    } catch (error) {
      set({ loading: false });
    }
  },

  addToCart: async (productId: string, quantity: number) => {
    try {
      await api.post('/cart', { productId, quantity });
      await get().fetchCart();
    } catch (error) {
      throw error;
    }
  },

  updateQuantity: async (id: string, quantity: number) => {
    try {
      await api.put(`/cart/${id}`, { quantity });
      await get().fetchCart();
    } catch (error) {
      throw error;
    }
  },

  removeFromCart: async (id: string) => {
    try {
      await api.delete(`/cart/${id}`);
      await get().fetchCart();
    } catch (error) {
      throw error;
    }
  },

  clearCart: async () => {
    try {
      await api.delete('/cart');
      set({ items: [] });
    } catch (error) {
      throw error;
    }
  },

  getTotal: () => {
    const { items } = get();
    return items.reduce((total, item) => {
      const price = item.product.salePrice || item.product.price;
      return total + price * item.quantity;
    }, 0);
  },
}));
