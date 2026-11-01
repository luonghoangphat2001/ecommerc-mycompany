import { useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import authService from '../services/authService';
import useSettingsStore from '../../../store/useSettingsStore';

const useResetPassword = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const translate = useSettingsStore((state) => state.translate);
  const [formData, setFormData] = useState({
    email: searchParams.get('email') || '',
    token: searchParams.get('token') || '',
    password: '',
    password_confirmation: ''
  });
  const [status, setStatus] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setStatus('');
    setError('');

    if (formData.password !== formData.password_confirmation) {
      setError(translate('auth.password_mismatch') || 'Password confirmation does not match');
      setIsLoading(false);
      return;
    }

    try {
      await authService.resetPassword(formData);
      setStatus(
        translate('auth.reset_password_success') ||
          'Your password has been reset successfully.'
      );
      setTimeout(() => navigate('/login'), 2000);
    } catch (err) {
      setError(
        err.response?.data?.message ||
          translate('auth.generic_error') ||
          'An error occurred, please try again.'
      );
    } finally {
      setIsLoading(false);
    }
  };

  return {
    formData,
    setFormData,
    status,
    error,
    isLoading,
    handleSubmit
  };
};

export default useResetPassword;
