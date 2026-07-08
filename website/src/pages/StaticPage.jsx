import React from 'react';
import { useLocation } from 'react-router-dom';

const staticContent = {
  'about': {
    title: 'Về chúng tôi',
    content: 'Nội dung đang được cập nhật...'
  },
  'contact': {
    title: 'Liên hệ',
    content: 'Nội dung đang được cập nhật...'
  },
  'shipping': {
    title: 'Chính sách vận chuyển',
    content: 'Nội dung đang được cập nhật...'
  },
  'returns': {
    title: 'Đổi trả & Hoàn tiền',
    content: 'Nội dung đang được cập nhật...'
  },
  'faq': {
    title: 'Câu hỏi thường gặp',
    content: 'Nội dung đang được cập nhật...'
  },
  'privacy': {
    title: 'Chính sách bảo mật',
    content: 'Nội dung đang được cập nhật...'
  },
  'terms': {
    title: 'Điều khoản sử dụng',
    content: 'Nội dung đang được cập nhật...'
  }
};

const StaticPage = () => {
  const location = useLocation();
  const page = location.pathname.replace('/', '') || 'about';
  const content = staticContent[page] || { title: '', content: '' };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <h1 className="text-3xl font-bold text-gray-900 mb-6">{content.title}</h1>
      <p className="text-gray-600">{content.content}</p>
    </div>
  );
};

export default StaticPage;
