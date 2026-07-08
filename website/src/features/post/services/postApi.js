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
    return apiService.get(`posts/categories/${categoryId}`);
  },

  getFeaturedPosts: () => {
    return apiService.get('posts/featured');
  },
};

export default postApi;
