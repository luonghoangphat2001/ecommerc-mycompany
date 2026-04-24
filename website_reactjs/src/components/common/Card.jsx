import React, { createContext, useContext } from 'react';

const CardContext = createContext();

const Card = ({ children, className = "" }) => {
  return (
    <CardContext.Provider value={{}}>
      <div className={`bg-white/60 backdrop-blur-xl border border-white/60 rounded-[2.5rem] overflow-hidden shadow-[0_8px_30px_rgba(0,0,0,0.02)] transition-all duration-300 ${className}`}>
        {children}
      </div>
    </CardContext.Provider>
  );
};

Card.Header = ({ children, className = "" }) => (
  <div className={`p-8 border-b border-slate-100 ${className}`}>
    {children}
  </div>
);

Card.Body = ({ children, className = "" }) => (
  <div className={`p-8 ${className}`}>
    {children}
  </div>
);

Card.Footer = ({ children, className = "" }) => (
  <div className={`p-8 border-t border-slate-100 bg-slate-50/50 ${className}`}>
    {children}
  </div>
);

Card.Title = ({ children, className = "" }) => (
  <h3 className={`text-xl font-bold text-slate-900 ${className}`}>
    {children}
  </h3>
);

export default Card;
