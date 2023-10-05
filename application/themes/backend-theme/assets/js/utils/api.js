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
    if (status === 405) {
      VUE.$router.push({
        name: 'dashboard'
      });
      VUE.$toasted.error('Please refresh your browser!');
    } else if (status === 403) {
      window.location.href = SITE_URL;
    }

    return Promise.reject(error.response);
  });

  return instance;
}