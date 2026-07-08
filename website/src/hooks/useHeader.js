import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../features/auth/store/useAuthStore';
import useCartStore from '../features/cart/store/useCartStore';
import useSettingsStore from '../store/useSettingsStore';

const useHeader = () => {
  const navigate = useNavigate();
  const { user, logout, _hasHydrated } = useAuthStore();
  const cartCount = useCartStore((state) => state.getCartCount());
  const { settings, language, setLanguage, currency, setCurrency, translate } = useSettingsStore();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  
  // Use cart store for cart open state
  const isCartOpen = useCartStore((state) => state.isCartOpen);
  const toggleCart = useCartStore((state) => state.toggleCart);
  const closeCart = useCartStore((state) => state.closeCart);

  const handleLogout = () => {
    if (window.confirm(translate('header.logout_confirm') || 'Are you sure?')) {
      logout();
      navigate('/');
    }
  };

  const toggleMobileMenu = () => setIsMobileMenuOpen(!isMobileMenuOpen);
  const closeMobileMenu = () => setIsMobileMenuOpen(false);

  return {
    user,
    _hasHydrated,
    cartCount,
    settings,
    language,
    currency,
    isMobileMenuOpen,
    isCartOpen,
    setLanguage,
    setCurrency,
    translate,
    handleLogout,
    toggleMobileMenu,
    closeMobileMenu,
    toggleCart,
    closeCart
  };
};

export default useHeader;
