import axiosClient from '../../../api/axiosClient';

const postApi = {
  getPosts: (params) => {
    return axiosClient.get('posts', { params });
  },

  getPostBySlug: (slug) => {
    return axiosClient.get(`posts/${slug}`);
  },

  getPostById: (id) => {
    return axiosClient.get(`posts/id/${id}`);
  },

  getPostsByCategory: (categoryId) => {
    return axiosClient.get(`posts/categories/${categoryId}`);
  },

  getFeaturedPosts: () => {
    return axiosClient.get('posts/featured');
  },
};

export default postApi;
