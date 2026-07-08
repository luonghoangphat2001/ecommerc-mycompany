import { useState } from 'react';
import authService from '../services/authService';

const useForgotPassword = () => {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setStatus('');
    setError('');

    try {
      await authService.forgotPassword(email);
      setStatus('Chúng tôi đã gửi link đặt lại mật khẩu vào email của bạn.');
    } catch (err) {
      setError(err.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.');
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
