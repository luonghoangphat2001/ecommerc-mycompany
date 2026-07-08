export const unwrapApiData = (response) => {
  if (response == null) {
    return null;
  }

  const isApiEnvelope = response
    && typeof response === 'object'
    && !Array.isArray(response)
    && 'data' in response
    && ('success' in response || 'meta' in response || 'message' in response || 'errors' in response);

  if (isApiEnvelope) {
    return response.data;
  }

  return response;
};

export const unwrapApiList = (response, fallback = []) => {
  const data = unwrapApiData(response);

  if (Array.isArray(data)) {
    return data;
  }

  if (data && Array.isArray(data.data)) {
    return data.data;
  }

  return fallback;
};

export const unwrapApiObject = (response, fallback = {}) => {
  const data = unwrapApiData(response);

  if (data && typeof data === 'object' && !Array.isArray(data)) {
    return data;
  }

  return fallback;
};

export class ApiService {
  constructor(client) {
    this.client = client;
  }

  async request(method, url, data = undefined, config = {}) {
    const response = await this.client.request({
      method,
      url,
      data,
      ...config,
    });

    return unwrapApiData(response);
  }

  get(url, config = {}) {
    return this.request('get', url, undefined, config);
  }

  post(url, data = undefined, config = {}) {
    return this.request('post', url, data, config);
  }

  put(url, data = undefined, config = {}) {
    return this.request('put', url, data, config);
  }

  patch(url, data = undefined, config = {}) {
    return this.request('patch', url, data, config);
  }

  delete(url, config = {}) {
    return this.request('delete', url, undefined, config);
  }
}
