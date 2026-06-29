import React from 'react';
import { useTranslation } from 'react-i18next';

const PostPagination = ({ currentPage, totalPages, onPageChange }) => {
  const { translate } = useTranslation('common');

  const containerClass = "flex items-center justify-center gap-2 mt-8";
  const buttonClass = "px-4 py-2 border rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed";
  const activeClass = "bg-blue-600 text-white border-blue-600";

  if (totalPages <= 1) return null;

  const getPageNumbers = () => {
    const pages = [];
    const maxVisible = 5;
    
    let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages, start + maxVisible - 1);
    
    if (end - start + 1 < maxVisible) {
      start = Math.max(1, end - maxVisible + 1);
    }
    
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    
    return pages;
  };

  return (
    <div className={containerClass}>
      <button
        className={buttonClass}
        onClick={() => onPageChange(currentPage - 1)}
        disabled={currentPage === 1}
      >
        {translate('previous')}
      </button>
      
      {getPageNumbers().map((page) => (
        <button
          key={page}
          className={`${buttonClass} ${currentPage === page ? activeClass : ''}`}
          onClick={() => onPageChange(page)}
        >
          {page}
        </button>
      ))}
      
      <button
        className={buttonClass}
        onClick={() => onPageChange(currentPage + 1)}
        disabled={currentPage === totalPages}
      >
        {translate('next')}
      </button>
    </div>
  );
};

export default PostPagination;
