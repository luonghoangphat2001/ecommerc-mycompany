import tokenManager from '../services/tokenManager';

describe('tokenManager', () => {
  beforeEach(() => tokenManager.clearAccessToken());

  test('keeps the access token in memory only', () => {
    tokenManager.setAccessToken('access-token');

    expect(tokenManager.getAccessToken()).toBe('access-token');
    expect(localStorage.getItem('access_token')).toBeNull();
    expect(localStorage.getItem('auth-storage')).toBeNull();
  });

  test('clears the access token', () => {
    tokenManager.setAccessToken('access-token');
    tokenManager.clearAccessToken();

    expect(tokenManager.getAccessToken()).toBeNull();
  });
});
