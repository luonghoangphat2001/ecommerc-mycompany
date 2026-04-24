import React from 'react';
import { Link } from 'react-router-dom';

const Logo = () => (
  <Link to="/" className="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 tracking-tight hover:opacity-80 transition-opacity">
    NovaStore
  </Link>
);

export default Logo;
