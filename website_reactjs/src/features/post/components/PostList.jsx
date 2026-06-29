import React from 'react';
import { useTranslation } from 'react-i18next';
import { LayoutGrid, List, Grid3X3, Newspaper } from 'lucide-react';
import PostCard from './PostCard';
import Skeleton from '../../../components/common/Skeleton';

const PostList = ({ 
  posts, 
  loading, 
  layout = 'grid', 
  columns = 3,
  showFeatured = true,
  onLayoutChange 
}) => {
  const { t: translate } = useTranslation('post');

  // Loading skeletons
  if (loading) {
    return (
      <div className={`grid gap-6 ${
        columns === 2 ? 'grid-cols-1 md:grid-cols-2' :
        columns === 4 ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4' :
        'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
      }`}>
        {Array.from({ length: 6 }).map((_, index) => (
          <Skeleton key={index} className="h-80 rounded-2xl" />
        ))}
      </div>
    );
  }

  if (!posts || posts.length === 0) {
    return (
      <div className="text-center py-16">
        <Newspaper size={64} className="mx-auto text-slate-300 mb-4" />
        <p className="text-slate-500 text-lg">
          {translate?.('no_posts') || 'Chưa có bài viết nào'}
        </p>
      </div>
    );
  }

  // Separate featured post if enabled
  const featuredPost = showFeatured && layout === 'grid' ? posts.find(p => p.is_featured) : null;
  const regularPosts = featuredPost 
    ? posts.filter(p => p.id !== featuredPost.id) 
    : posts;

  // Grid layout (default)
  if (layout === 'grid') {
    return (
      <div className="space-y-8">
        {/* Layout Controls */}
        {onLayoutChange && (
          <div className="flex items-center justify-between mb-6">
            <p className="text-slate-500 text-sm">
              {posts.length} {translate?.('posts_found') || 'bài viết'}
            </p>
            <div className="flex items-center gap-2">
              <button 
                onClick={() => onLayoutChange('grid')}
                className={`p-2 rounded-lg transition-colors ${layout === 'grid' ? 'bg-blue-100 text-blue-600' : 'hover:bg-slate-100'}`}
              >
                <Grid3X3 size={20} />
              </button>
              <button 
                onClick={() => onLayoutChange('list')}
                className={`p-2 rounded-lg transition-colors ${layout === 'list' ? 'bg-blue-100 text-blue-600' : 'hover:bg-slate-100'}`}
              >
                <List size={20} />
              </button>
            </div>
          </div>
        )}

        {/* Featured Post */}
        {featuredPost && (
          <div className="mb-8">
            <PostCard post={featuredPost} variant="horizontal" />
          </div>
        )}

        {/* Regular Posts Grid */}
        <div className={`grid gap-6 ${
          columns === 2 ? 'grid-cols-1 md:grid-cols-2' :
          columns === 4 ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4' :
          'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
        }`}>
          {regularPosts.map((post) => (
            <PostCard key={post.id} post={post} variant="default" />
          ))}
        </div>
      </div>
    );
  }

  // List layout
  if (layout === 'list') {
    return (
      <div className="space-y-6">
        {/* Layout Controls */}
        {onLayoutChange && (
          <div className="flex items-center justify-between mb-6">
            <p className="text-slate-500 text-sm">
              {posts.length} {translate?.('posts_found') || 'bài viết'}
            </p>
            <div className="flex items-center gap-2">
              <button 
                onClick={() => onLayoutChange('grid')}
                className={`p-2 rounded-lg transition-colors ${layout === 'grid' ? 'bg-blue-100 text-blue-600' : 'hover:bg-slate-100'}`}
              >
                <Grid3X3 size={20} />
              </button>
              <button 
                onClick={() => onLayoutChange('list')}
                className={`p-2 rounded-lg transition-colors ${layout === 'list' ? 'bg-blue-100 text-blue-600' : 'hover:bg-slate-100'}`}
              >
                <List size={20} />
              </button>
            </div>
          </div>
        )}

        {/* Posts as horizontal cards */}
        {posts.map((post) => (
          <PostCard key={post.id} post={post} variant="horizontal" />
        ))}
      </div>
    );
  }

  // Compact layout (for sidebars)
  if (layout === 'compact') {
    return (
      <div className="space-y-4">
        {posts.slice(0, 5).map((post) => (
          <PostCard key={post.id} post={post} variant="compact" />
        ))}
      </div>
    );
  }

  // Default fallback
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {posts.map((post) => (
        <PostCard key={post.id} post={post} />
      ))}
    </div>
  );
};

export default PostList;
