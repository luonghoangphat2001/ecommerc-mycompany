import React from 'react';
import PostList from '../features/post/components/PostList';
import { usePostList } from '../features/post/hooks/usePostList';

const PostsPage = () => {
  const { data: postsData, isLoading } = usePostList({ per_page: 12 });
  const posts = postsData?.data?.data || postsData?.data || [];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Blog</h1>
      <PostList posts={posts} loading={isLoading} />
    </div>
  );
};

export default PostsPage;
