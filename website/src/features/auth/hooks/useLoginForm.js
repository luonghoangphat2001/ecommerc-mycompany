import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../store/useAuthStore';

const DEFAULT_ADMIN_EMAIL =
  process.env.REACT_APP_ADMIN_EMAIL ||
  process.env.REACT_APP_ADMIN_USER ||
  'admin@admin.com';

const DEFAULT_ADMIN_PASSWORD =
  process.env.REACT_APP_ADMIN_PASSWORD ||
  process.env.REACT_APP_ADMIN_PASS ||
  'password';

const useLoginForm = () => {
  const [email, setEmail] = useState(DEFAULT_ADMIN_EMAIL);
  const [password, setPassword] = useState(DEFAULT_ADMIN_PASSWORD);
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const login = useAuthStore((state) => state.login);

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');

    try {
      await login(email, password);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed');
    }
  };

  return {
    email,
    setEmail,
    password,
    setPassword,
    error,
    handleLogin
  };
};

export default useLoginForm;
