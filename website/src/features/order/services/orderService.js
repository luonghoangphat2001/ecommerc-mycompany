import apiService from '../../../api/apiService';

const API_URL = 'orders';

const orderService = {
  getAll: (params) => {
    return apiService.get(API_URL, { params });
  },

  getById: (id) => {
    const url = `${API_URL}/${id}`;
    return apiService.get(url);
  },

  create: (data) => {
    return apiService.post(API_URL, data);
  },

  update: (id, data) => {
    const url = `${API_URL}/${id}`;
    return apiService.put(url, data);
  },

  delete: (id) => {
    const url = `${API_URL}/${id}`;
    return apiService.delete(url);
  },

  cancelOrder: (id, reason = '') => {
    return apiService.post(`${API_URL}/${id}/cancel`, { reason });
  },

  trackOrder: async (code) => {
    const cleanCode = String(code || '').trim().replace(/^#/, '');
    try {
      return await apiService.get(`${API_URL}/track/${encodeURIComponent(cleanCode)}`);
    } catch (error) {
      if (error?.response?.status === 404) {
        const listRes = await apiService.get(API_URL, {
          params: { include: 'items,payments' },
        });
        const list = listRes?.data?.data || listRes?.data || [];
        const matched = Array.isArray(list)
          ? list.find(
              (o) =>
                String(o.number || '').toLowerCase() === cleanCode.toLowerCase() ||
                String(o.id) === cleanCode
            )
          : null;
        if (matched) {
          return { data: matched };
        }
      }
      throw error;
    }
  },
};

export default orderService;
