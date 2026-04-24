import React from 'react';

const Loading = ({ message }) => (
  <div className="w-full h-96 flex flex-col items-center justify-center gap-4">
    <div className="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
    {message && <p className="text-slate-500 font-medium">{message}</p>}
  </div>
);

export default Loading;
