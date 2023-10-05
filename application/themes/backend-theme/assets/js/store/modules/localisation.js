import _ from 'lodash/array';

const state = {
  i18n: [],
};

const getters = {
  
};

const actions = {
  addI18n({ state, commit }, i18n) {
    commit('pushI18n', i18n);
  },
  isLoaded({state, commit}, module) {
    const find = _.findIndex(state.i18n, function(o) {
      return o === module;
    });

    if (find < 0) {
      return false;
    } else {
      return true;
    }
  }
};

const mutations = {
  pushI18n (state, i18n) {
    state.i18n.push(i18n);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}