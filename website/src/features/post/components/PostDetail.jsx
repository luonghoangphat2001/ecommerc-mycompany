import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';
import { Link } from 'react-router-dom';
import { formatDate } from '../../../utils/date';

const PostDetail = ({ post }) => {
  const translate = useSettingsStore(state => state.translate);

  const containerClass = "max-w-4xl mx-auto py-8 px-4";
  const headerClass = "mb-8";
  const categoryClass = "text-sm text-blue-600 font-medium";
  const titleClass = "text-3xl font-bold mt-4 mb-4";
  const metaClass = "flex items-center gap-4 text-sm text-gray-500";
  const imageClass = "w-full h-64 md:h-96 object-cover rounded-lg mb-8";
  const contentClass = "prose max-w-none";
  const backClass = "text-blue-600 hover:text-blue-800 mb-4 inline-block";

  if (!post) return null;

  // Use image_url from API (like Product)
  const postImage = post.image_url || post.image?.url || post.featured_image;
  const postCategory = post.category || post.categories?.[0];

  return (
    <div className={containerClass}>
      <Link to="/posts" className={backClass}>← {translate('back_to_posts')}</Link>
      
      <div className={headerClass}>
        {postCategory && <span className={categoryClass}>{postCategory.name}</span>}
        <h1 className={titleClass}>{post.title}</h1>
        <div className={metaClass}>
          <span>{translate('author')}: {post.author?.name}</span>
          <span>{translate('published_at')}: {formatDate(post.published_at)}</span>
        </div>
      </div>

      {postImage ? (
        <img 
          src={postImage} 
          alt={post.title} 
          className={imageClass}
          onError={(e) => { e.target.style.display = 'none'; }}
        />
      ) : (
        <div className={`${imageClass} bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center`}>
          <span className="text-6xl">📝</span>
        </div>
      )}

      <div 
        className={contentClass}
        dangerouslySetInnerHTML={{ __html: post.content }}
      />
    </div>
  );
};

export default PostDetail;
