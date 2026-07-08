import apiService from '../../../api/apiService';

const menuApi = {
  getAllMenus: () => {
    return apiService.get('menus');
  },

  getMenuBySlug: (slug) => {
    return apiService.get(`menus/${slug}`);
  },
};

export default menuApi;
