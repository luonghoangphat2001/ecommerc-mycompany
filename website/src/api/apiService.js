import axiosClient from './axiosClient';
import { ApiService } from './apiResponse';

const apiService = new ApiService(axiosClient);

export default apiService;
