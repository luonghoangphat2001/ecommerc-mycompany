import React, { lazy, Suspense, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
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
const PostsPage = lazy(() => import('./pages/PostsPage'));
const PostDetailPage = lazy(() => import('./pages/PostDetailPage'));
const StaticPage = lazy(() => import('./pages/StaticPage'));

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
          <Toaster position="top-right" />
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPasswordPage />} />
            <Route path="/reset-password" element={<ResetPasswordPage />} />
            
            <Route path="/" element={<Layout />}>


              <Route index element={<HomePage />} />
              <Route path="shop" element={<ShopPage />} />
              <Route path="products/:slug" element={<ProductDetailPage />} />
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
              
              {/* Blog & Static Pages */}
              <Route path="posts" element={<PostsPage />} />
              <Route path="posts/:slug" element={<PostDetailPage />} />
              <Route path="blog" element={<PostsPage />} />
              <Route path="blog/:slug" element={<PostDetailPage />} />

              <Route path="pages/:slug" element={<StaticPage />} />
              <Route path="p/:slug" element={<StaticPage />} />
              <Route path="pages" element={<Navigate to="/pages/about" replace />} />

              {/* Direct Aliases */}
              <Route path="about" element={<StaticPage slug="about" />} />
              <Route path="contact" element={<StaticPage slug="contact" />} />
              <Route path="shipping" element={<StaticPage slug="shipping" />} />
              <Route path="returns" element={<StaticPage slug="returns" />} />
              <Route path="faq" element={<StaticPage slug="faq" />} />
              <Route path="privacy" element={<StaticPage slug="privacy" />} />
              <Route path="terms" element={<StaticPage slug="terms" />} />

              {/* Catch-all fallback */}
              <Route path="*" element={<Navigate to="/" replace />} />
            </Route>
          </Routes>
        </Suspense>
      </ErrorBoundary>
    </BrowserRouter>
  );
}

export default App;
