import React from 'react';
import NavLink from '../common/NavLink';


const Navbar = ({ t }) => (
  <nav className="hidden md:flex items-center gap-6">
    <NavLink to="/">{t('header.home')}</NavLink>
    <NavLink to="/shop">{t('header.shop')}</NavLink>
  </nav>
);

export default Navbar;
