import axiosClient from '../../../api/axiosClient';

const addressService = {
  // Location/Geography APIs
  getCountries: (config = {}) => {
    return axiosClient.get('countries', config);
  },

  getStates: (countryCode, config = {}) => {
    const url = `countries/${countryCode}/states`;
    return axiosClient.get(url, config);
  },

  getRegions: (countryCode, stateId, config = {}) => {
    const url = `countries/${countryCode}/states/${stateId}/regions`;
    return axiosClient.get(url, config);
  },

  getSubRegions: (countryCode, stateId, regionId, config = {}) => {
    const url = `countries/${countryCode}/states/${stateId}/regions/${regionId}/sub-regions`;
    return axiosClient.get(url, config);
  },

  // User Address APIs
  listUserAddresses: () => {
    return axiosClient.get('user/addresses');
  },

  createUserAddress: (data) => {
    return axiosClient.post('user/addresses', data);
  },

  updateUserAddress: (id, data) => {
    return axiosClient.put(`user/addresses/${id}`, data);
  },

  deleteUserAddress: (id) => {
    return axiosClient.delete(`user/addresses/${id}`);
  }
};

export default addressService;
