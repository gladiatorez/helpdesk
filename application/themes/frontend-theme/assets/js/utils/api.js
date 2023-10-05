import axios from 'axios';

export default () => {
  const instance = axios.create({
    baseURL: API_URL,
    withCredentials: false,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  });

  instance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  instance.interceptors.response.use((response) => {
    return response;
  }, function (error) {
    const { status, statusText } = error.response;
    return Promise.reject(error.response);
  });

  return instance;
}