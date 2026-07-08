import apiService from '../../../api/apiService';

const productApi = {
  getAllProducts: (params) => {
    return apiService.get('products', { params });
  },

  getProductById: (id) => {
    return apiService.get(`products/${id}`);
  },

  getProductBySlug: (slug) => {
    return apiService.get(`products/by-slug/${slug}`);
  },

  getProductsByCategory: (categoryId) => {
    return apiService.get(`product-categories/${categoryId}/products`);
  },

  getInventory: (productId, variantId = null) => {
    const params = variantId ? { variant_id: variantId } : {};
    return apiService.get(`products/${productId}/inventory`, { params });
  },

  getRecommendations: (productId) => {
    return apiService.get(`products/${productId}/recommendations`);
  },

  getUpsellProducts: (productId) => {
    return apiService.get(`products/${productId}/upsells`);
  },

  getCrossSellProducts: (productId) => {
    return apiService.get(`products/${productId}/cross-sells`);
  },

  getRelatedProducts: (productId) => {
    return apiService.get(`products/${productId}/related`);
  }
};


export default productApi;
