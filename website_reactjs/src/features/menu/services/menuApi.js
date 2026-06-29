import axiosClient from '../../../api/axiosClient';

const menuApi = {
  getAllMenus: () => {
    return axiosClient.get('menus');
  },

  getMenuBySlug: (slug) => {
    return axiosClient.get(`menus/${slug}`);
  },
};

export default menuApi;
