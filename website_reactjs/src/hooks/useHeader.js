import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../features/auth/store/useAuthStore';
import useCartStore from '../features/cart/store/useCartStore';
import useSettingsStore from '../store/useSettingsStore';

const useHeader = () => {
  const navigate = useNavigate();
  const { user, logout, _hasHydrated } = useAuthStore();
  const cartCount = useCartStore((state) => state.getCartCount());
  const { settings, language, setLanguage, currency, setCurrency, t } = useSettingsStore();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const handleLogout = () => {
    if (window.confirm(t('header.logout_confirm') || 'Are you sure?')) {
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
    setLanguage,
    setCurrency,
    t,
    handleLogout,
    toggleMobileMenu,
    closeMobileMenu
  };
};

export default useHeader;
