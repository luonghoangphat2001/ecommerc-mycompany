import React from 'react';
import { Link } from 'react-router-dom';

const NavLink = ({ to, children, onClick }) => (
  <Link 
    to={to} 
    onClick={onClick}
    className="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors"
  >
    {children}
  </Link>
);

export default NavLink;
