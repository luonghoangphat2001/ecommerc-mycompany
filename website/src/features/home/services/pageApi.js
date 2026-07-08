import axiosClient from '../../../api/axiosClient';

const pageApi = {
  getHomeContent: () => {
    return axiosClient.get('pages/home');
  },

  getAboutContent: () => {
    return axiosClient.get('pages/about');
  },

  getPageBySlug: (slug) => {
    return axiosClient.get(`pages/${slug}`);
  },
};

export default pageApi;
