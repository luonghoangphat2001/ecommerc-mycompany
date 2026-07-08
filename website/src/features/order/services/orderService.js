// src/services/orderService.js
import axiosClient from '../../../api/axiosClient';

const API_URL = 'orders';

const orderService = {
  getAll: (params) => {
    return axiosClient.get(API_URL, { params });
  },

  getById: (id) => {
    const url = `${API_URL}/${id}`;
    return axiosClient.get(url);
  },

  create: (data) => {
    return axiosClient.post(API_URL, data);
  },

  update: (id, data) => {
    const url = `${API_URL}/${id}`;
    return axiosClient.put(url, data);
  },

  delete: (id) => {
    const url = `${API_URL}/${id}`;
    return axiosClient.delete(url);
  },

  // -- MOCK DATA DỰ PHÒNG NẾU SERVER JSON LỖI HOẶC CHƯA CHẠY -- //
  getAllMock: async () => {
    return new Promise((resolve) => setTimeout(() => resolve([
      { id: 'ORD-001', customer: 'Nguyễn Văn A', date: '2024-04-05', total: 1200, status: 'Hoàn thành' },
      { id: 'ORD-002', customer: 'Trần Thị B', date: '2024-04-04', total: 1099, status: 'Đang xử lý' },
    ]), 500));
  }
};

export default orderService;
