import axiosClient from '../../../api/axiosClient';

const productApi = {
  getAllProducts: (params) => {
    return axiosClient.get('products', { params });
  },

  getProductById: (id) => {
    return axiosClient.get(`products/${id}`);
  },

  getProductBySlug: (slug) => {
    return axiosClient.get(`products/by-slug/${slug}`);
  },

  getProductsByCategory: (categoryId) => {
    return axiosClient.get(`product-categories/${categoryId}/products`);
  },

  getInventory: (productId, variantId = null) => {
    const params = variantId ? { variant_id: variantId } : {};
    return axiosClient.get(`products/${productId}/inventory`, { params });
  },

  getRecommendations: (productId) => {
    return axiosClient.get(`products/${productId}/recommendations`);
  },

  getUpsellProducts: (productId) => {
    return axiosClient.get(`products/${productId}/upsells`);
  },

  getCrossSellProducts: (productId) => {
    return axiosClient.get(`products/${productId}/cross-sells`);
  },

  getRelatedProducts: (productId) => {
    return axiosClient.get(`products/${productId}/related`);
  }
};


export default productApi;
