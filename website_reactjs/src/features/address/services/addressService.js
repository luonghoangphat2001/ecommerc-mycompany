import axiosClient from '../../../api/axiosClient';

const addressService = {
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
  }
};

export default addressService;
