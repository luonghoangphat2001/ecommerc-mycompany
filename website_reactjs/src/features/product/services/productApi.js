import axiosClient from '../../../api/axiosClient';


const productApi = {
  getAllProducts: (params) => {
    return axiosClient.get('products', { params });
  },

  getProductById: (id) => {
    return axiosClient.get(`products/${id}`);
  },

  getProductsByCategory: (categoryId) => {
    return axiosClient.get(`product-categories/${categoryId}/products`);
  }
};


export default productApi;
