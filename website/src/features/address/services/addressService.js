import apiService from '../../../api/apiService';

const addressService = {
  // Location/Geography APIs
  getCountries: (config = {}) => {
    return apiService.get('countries', config);
  },

  getStates: (countryCode, config = {}) => {
    const url = `countries/${countryCode}/states`;
    return apiService.get(url, config);
  },

  getRegions: (countryCode, stateId, config = {}) => {
    const url = `countries/${countryCode}/states/${stateId}/regions`;
    return apiService.get(url, config);
  },

  getSubRegions: (countryCode, stateId, regionId, config = {}) => {
    const url = `countries/${countryCode}/states/${stateId}/regions/${regionId}/sub-regions`;
    return apiService.get(url, config);
  },

  // User Address APIs
  listUserAddresses: () => {
    return apiService.get('user/addresses');
  },

  createUserAddress: (data) => {
    return apiService.post('user/addresses', data);
  },

  updateUserAddress: (id, data) => {
    return apiService.put(`user/addresses/${id}`, data);
  },

  deleteUserAddress: (id) => {
    return apiService.delete(`user/addresses/${id}`);
  }
};

export default addressService;
