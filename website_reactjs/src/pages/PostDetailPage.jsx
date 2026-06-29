import React from 'react';
import { useParams } from 'react-router-dom';
import PostDetail from '../features/post/components/PostDetail';
import { usePostDetail } from '../features/post/hooks/usePostDetail';

const PostDetailPage = () => {
  const { slug } = useParams();
  const { data: postData, isLoading, error } = usePostDetail(slug);
  
  const post = postData?.data;

  if (isLoading) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-8">
        <div className="animate-pulse">
          <div className="h-8 bg-slate-200 rounded w-3/4 mb-4"></div>
          <div className="h-4 bg-slate-200 rounded w-1/2 mb-8"></div>
          <div className="h-64 bg-slate-200 rounded mb-8"></div>
          <div className="space-y-3">
            <div className="h-4 bg-slate-200 rounded"></div>
            <div className="h-4 bg-slate-200 rounded"></div>
            <div className="h-4 bg-slate-200 rounded w-5/6"></div>
          </div>
        </div>
      </div>
    );
  }

  if (error || !post) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-8 text-center">
        <p className="text-slate-500">Không tìm thấy bài viết</p>
      </div>
    );
  }

  return <PostDetail post={post} />;
};

export default PostDetailPage;
