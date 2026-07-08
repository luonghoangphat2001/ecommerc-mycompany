import apiService from '../../../api/apiService';

const pageApi = {
  getHomeContent: () => {
    return apiService.get('pages/home');
  },

  getAboutContent: () => {
    return apiService.get('pages/about');
  },

  getPageBySlug: (slug) => {
    return apiService.get(`pages/${slug}`);
  },
};

export default pageApi;
