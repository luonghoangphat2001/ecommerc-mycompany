import apiService from '../../../api/apiService';

const postApi = {
  getPosts: (params) => {
    return apiService.get('posts', { params });
  },

  getPostBySlug: (slug) => {
    return apiService.get(`posts/${slug}`);
  },

  getPostById: (id) => {
    return apiService.get(`posts/id/${id}`);
  },

  getPostsByCategory: (categoryId) => {
    return apiService.get(`post-categories/${categoryId}/posts`);
  },

  getFeaturedPosts: () => {
    return apiService.get('posts/featured');
  },
};

export default postApi;
