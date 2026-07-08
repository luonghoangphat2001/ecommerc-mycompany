import React from 'react';
import { useLocation } from 'react-router-dom';
import { usePageBySlug } from '../features/home/hooks/useHomeData';

const fallbackContent = {
  about: {
    title: 'Về chúng tôi',
    content: 'Nội dung đang được cập nhật...'
  },
  contact: {
    title: 'Liên hệ',
    content: 'Nội dung đang được cập nhật...'
  },
  shipping: {
    title: 'Chính sách vận chuyển',
    content: 'Nội dung đang được cập nhật...'
  },
  returns: {
    title: 'Đổi trả & Hoàn tiền',
    content: 'Nội dung đang được cập nhật...'
  },
  faq: {
    title: 'Câu hỏi thường gặp',
    content: 'Nội dung đang được cập nhật...'
  },
  privacy: {
    title: 'Chính sách bảo mật',
    content: 'Nội dung đang được cập nhật...'
  },
  terms: {
    title: 'Điều khoản sử dụng',
    content: 'Nội dung đang được cập nhật...'
  }
};

const renderBlock = (block, index) => {
  const type = block?.type || block?.block_type || 'text';
  const value = block?.value ?? block?.content ?? block?.text ?? block?.body ?? '';
  const mediaUrl = block?.url || block?.media_url || block?.value?.url || block?.value?.path || '';

  switch (type) {
    case 'media':
      return (
        <div key={index} className="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-sm">
          {mediaUrl ? (
            <img src={mediaUrl} alt={block?.alt || block?.caption || 'media'} className="w-full h-auto" />
          ) : (
            <div className="p-6 text-slate-500">Media block</div>
          )}
          {block?.caption && <div className="p-4 text-sm text-slate-500">{block.caption}</div>}
        </div>
      );
    case 'textarea':
      return (
        <div key={index} className="prose prose-slate max-w-none bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p className="whitespace-pre-line">{value}</p>
        </div>
      );
    case 'code-editor':
      return (
        <pre key={index} className="overflow-x-auto rounded-2xl bg-slate-950 text-slate-100 p-6 text-sm">
          <code>{String(value)}</code>
        </pre>
      );
    case 'number':
      return (
        <div key={index} className="rounded-2xl border border-slate-200 bg-white p-6 text-3xl font-bold text-slate-900 shadow-sm">
          {value}
        </div>
      );
    default:
      return (
        <div key={index} className="prose prose-slate max-w-none bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p className="whitespace-pre-line">{value}</p>
        </div>
      );
  }
};

const StaticPage = () => {
  const location = useLocation();
  const pageSlug = (location.pathname.replace(/^\/+/, '') || 'about').replace(/^p\//, '');
  const { data } = usePageBySlug(pageSlug);
  const page = data?.data || data || null;
  const content = page || fallbackContent[pageSlug] || { title: '', content: '' };
  const blocks = Array.isArray(page?.blocks) ? page.blocks : [];

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <h1 className="text-3xl font-bold text-gray-900 mb-6">{content.title}</h1>
      {content.content && <p className="text-gray-600 mb-8">{content.content}</p>}

      {blocks.length > 0 ? (
        <div className="space-y-6">
          {blocks.map((block, index) => renderBlock(block, index))}
        </div>
      ) : (
        !content.content && <p className="text-gray-600">Nội dung đang được cập nhật...</p>
      )}
    </div>
  );
};

export default StaticPage;
