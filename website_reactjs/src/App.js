import React, { lazy, Suspense, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './components/layout/Layout';
import { Loading, ErrorBoundary } from './components/common';

import useSettingsStore from './store/useSettingsStore';
import useAuthStore from './features/auth/store/useAuthStore';

// Lazy-loaded pages (Code Splitting)
const HomePage = lazy(() => import('./pages/HomePage'));
const ShopPage = lazy(() => import('./pages/ShopPage'));
const ProductDetailPage = lazy(() => import('./pages/ProductDetailPage'));
const CartPage = lazy(() => import('./pages/CartPage'));
const LoginPage = lazy(() => import('./pages/Login/LoginPage'));
const RegisterPage = lazy(() => import('./pages/Login/RegisterPage'));
const ForgotPasswordPage = lazy(() => import('./pages/Login/ForgotPasswordPage'));
const ResetPasswordPage = lazy(() => import('./pages/Login/ResetPasswordPage'));
const CheckoutPage = lazy(() => import('./pages/CheckoutPage'));
const OrderSuccessPage = lazy(() => import('./pages/OrderSuccessPage'));
const MyAccountPage = lazy(() => import('./pages/MyAccountPage'));

function App() {
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const fetchSettings = useSettingsStore((state) => state.fetchSettings);

  useEffect(() => {
    fetchSettings();
  }, [fetchSettings]);

  return (
    <BrowserRouter>
      <ErrorBoundary>
        <Suspense fallback={<Loading />}>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPasswordPage />} />
            <Route path="/reset-password" element={<ResetPasswordPage />} />
            
            <Route path="/" element={<Layout />}>


              <Route index element={<HomePage />} />
              <Route path="shop" element={<ShopPage />} />
              <Route path="products/:id" element={<ProductDetailPage />} />
              <Route path="cart" element={<CartPage />} />
              
              <Route 
                path="checkout" 
                element={isAuthenticated ? <CheckoutPage /> : <Navigate to="/login" />} 
              />
              <Route 
                path="checkout/success" 
                element={<OrderSuccessPage />} 
              />
              <Route 
                path="my-account" 
                element={isAuthenticated ? <MyAccountPage /> : <Navigate to="/login" />} 
              />
            </Route>
          </Routes>
        </Suspense>
      </ErrorBoundary>
    </BrowserRouter>
  );
}

export default App;
