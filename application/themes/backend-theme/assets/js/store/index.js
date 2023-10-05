import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);

import localisation from './modules/localisation';
import layouts from './modules/layouts';
import userLogin from './modules/userLogin';

const debug = process.env.NODE_ENV !== 'production';
export default new Vuex.Store({
  modules: {
    layouts,
    localisation,
    userLogin,
  },
  strict: debug,
})