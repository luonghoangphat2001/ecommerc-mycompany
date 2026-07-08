import useAuthStore from '../store/useAuthStore';

const useAuth = () => {
  const { user, isAuthenticated, isLoading, error, login, logout } = useAuthStore();

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    login,
    logout
  };
};

export default useAuth;
