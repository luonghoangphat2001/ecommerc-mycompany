let accessToken = null;

const tokenManager = {
  getAccessToken: () => accessToken,
  setAccessToken: (token) => {
    accessToken = token || null;
  },
  clearAccessToken: () => {
    accessToken = null;
  },
};

export default tokenManager;
