import { useState } from 'react';
import authService from '../services/authService';
import useSettingsStore from '../../../store/useSettingsStore';

const useForgotPassword = () => {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const translate = useSettingsStore((state) => state.translate);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setStatus('');
    setError('');

    try {
      await authService.forgotPassword(email);
      setStatus(
        translate('auth.forgot_password_sent') ||
          'We have emailed your password reset link.'
      );
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
    email,
    setEmail,
    status,
    error,
    isLoading,
    handleSubmit
  };
};

export default useForgotPassword;
