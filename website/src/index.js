import React from 'react';
import ReactDOM from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import './index.css';
import App from './App';

// Suppress runtime errors injected by 3rd-party browser extensions
if (typeof window !== 'undefined') {
  window.addEventListener(
    'error',
    (event) => {
      if (
        event?.filename?.includes('chrome-extension://') ||
        event?.error?.stack?.includes('chrome-extension://')
      ) {
        event.stopImmediatePropagation();
        event.preventDefault();
      }
    },
    true
  );
  window.addEventListener(
    'unhandledrejection',
    (event) => {
      if (event?.reason?.stack?.includes('chrome-extension://')) {
        event.stopImmediatePropagation();
        event.preventDefault();
      }
    },
    true
  );
}

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
      staleTime: 5 * 60 * 1000,
    },
  },
});

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <QueryClientProvider client={queryClient}>
    <App />
  </QueryClientProvider>
);

