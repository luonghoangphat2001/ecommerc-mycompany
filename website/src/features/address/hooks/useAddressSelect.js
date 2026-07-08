import { useState, useEffect, useCallback, useRef } from 'react';
import addressService from '../services/addressService';
import { unwrapApiObject } from '../../../api/apiResponse';

const useAddressSelect = () => {
  const [countries, setCountries] = useState({});
  const [states, setStates] = useState({});
  const [regions, setRegions] = useState({});
  const [subRegions, setSubRegions] = useState({});
  const [loading, setLoading] = useState(false);
  const abortControllerRef = useRef(null);

  const fetchCountries = useCallback(async () => {
    if (abortControllerRef.current) {
      abortControllerRef.current.abort();
    }
    abortControllerRef.current = new AbortController();

    try {
      setLoading(true);
      const response = await addressService.getCountries({ signal: abortControllerRef.current.signal });
      setCountries(unwrapApiObject(response, {}));
    } catch (error) {
      if (error.name !== 'CanceledError') {
        console.error('Failed to fetch countries:', error);
        // Fallback to hardcoded countries
        setCountries({
          'VN': 'Việt Nam',
          'US': 'United States',
          'JP': 'Japan',
          'CN': 'China',
          'KR': 'South Korea',
        });
      }
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchStates = useCallback(async (countryCode) => {
    if (!countryCode) return;
    try {
      setLoading(true);
      const response = await addressService.getStates(countryCode);
      setStates(unwrapApiObject(response, {}));
    } catch (error) {
      console.error('Failed to fetch states:', error);
      // Fallback for VN
      if (countryCode === 'VN') {
        setStates({
          '01': 'Thành phố Hà Nội',
          '79': 'Thành phố Hồ Chí Minh',
          '48': 'Đà Nẵng',
          '92': 'Cần Thơ',
          '43': 'An Giang',
          '44': 'Bà Rịa - Vũng Tàu',
          '45': 'Bắc Giang',
          '46': 'Bắc Kạn',
          '47': 'Bạc Liêu',
          '49': 'Bến Tre',
          '50': 'Bình Dương',
          '51': 'Bình Định',
          '52': 'Bình Phước',
          '53': 'Bình Thuận',
          '54': 'Cà Mau',
          '55': 'Cao Bằng',
          '56': 'Đắk Lắk',
          '57': 'Đắk Nông',
          '58': 'Điện Biên',
          '59': 'Đồng Nai',
          '60': 'Đồng Tháp',
          '61': 'Gia Lai',
          '62': 'Hà Giang',
          '63': 'Hà Nam',
          '64': 'Hà Tĩnh',
          '65': 'Hải Dương',
          '66': 'Hải Phòng',
          '67': 'Hậu Giang',
          '68': 'Hòa Bình',
          '69': 'Hưng Yên',
          '70': 'Khánh Hòa',
          '71': 'Kiên Giang',
          '72': 'Kon Tum',
          '73': 'Lai Châu',
          '74': 'Lâm Đồng',
          '75': 'Lạng Sơn',
          '76': 'Lào Cai',
          '77': 'Long An',
          '78': 'Nam Định',
          '80': 'Nghệ An',
          '81': 'Ninh Bình',
          '82': 'Ninh Thuận',
          '83': 'Phú Thọ',
          '84': 'Phú Yên',
          '85': 'Quảng Bình',
          '86': 'Quảng Nam',
          '87': 'Quảng Ngãi',
          '88': 'Quảng Ninh',
          '89': 'Quảng Trị',
          '90': 'Sóc Trăng',
          '91': 'Sơn La',
          '93': 'Tây Ninh',
          '94': 'Thái Bình',
          '95': 'Thái Nguyên',
          '96': 'Thanh Hóa',
          '97': 'Thừa Thiên Huế',
          '98': 'Tiền Giang',
          '99': 'Trà Vinh',
          '100': 'Tuyên Quang',
          '101': 'Vĩnh Long',
          '102': 'Vĩnh Phúc',
          '103': 'Yên Bái',
        });
      }
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchRegions = useCallback(async (countryCode, stateId) => {
    if (!countryCode || !stateId) return;
    try {
      setLoading(true);
      const response = await addressService.getRegions(countryCode, stateId);
      setRegions(unwrapApiObject(response, {}));
    } catch (error) {
      console.error('Failed to fetch regions:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchSubRegions = useCallback(async (countryCode, stateId, regionId) => {
    if (!countryCode || !stateId || !regionId) return;
    try {
      setLoading(true);
      const response = await addressService.getSubRegions(countryCode, stateId, regionId);
      setSubRegions(unwrapApiObject(response, {}));
    } catch (error) {
      console.error('Failed to fetch sub-regions:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchCountries();
    return () => {
      if (abortControllerRef.current) {
        abortControllerRef.current.abort();
      }
    };
  }, [fetchCountries]);

  return {
    countries,
    states,
    regions,
    subRegions,
    loading,
    fetchStates,
    fetchRegions,
    fetchSubRegions
  };
};

export default useAddressSelect;
