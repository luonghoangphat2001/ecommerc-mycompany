import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import authService from '../services/authService';
import useAuthStore from '../store/useAuthStore';
import useSettingsStore from '../../../store/useSettingsStore';

const useRegisterForm = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: ''
  });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const navigate = useNavigate();
  const setUser = useAuthStore((state) => state.setUser);
  const translate = useSettingsStore((state) => state.translate);

  const handleRegister = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    if (formData.password !== formData.password_confirmation) {
      setError(translate('auth.password_mismatch') || 'Password confirmation does not match');
      setIsLoading(false);
      return;
    }
    try {
      const response = await authService.register(formData);
      const authData = response?.data || response;
      const user = authData?.user || (authData?.id ? authData : null);
      const accessToken = authData?.access_token || null;

      toast.success(translate('auth.register_success') || 'Account registered successfully!');

      if (user && accessToken) {
        setUser(user, accessToken);
        navigate('/');
      } else if (user) {
        setUser(user);
        navigate('/');
      } else {
        navigate('/login');
      }
    } catch (err) {
      setError(
        err.response?.data?.message ||
          translate('auth.register_failed') ||
          'Registration failed, please try again'
      );
    } finally {
      setIsLoading(false);
    }
  };

  return {
    formData,
    setFormData,
    error,
    handleRegister,
    isLoading
  };
};

export default useRegisterForm;
