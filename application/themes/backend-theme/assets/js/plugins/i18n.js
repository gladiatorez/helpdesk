import Vue from 'vue'
import vuexI18n from 'vuex-i18n'
import store from '../store'

Vue.use(vuexI18n.plugin, store, {
  translateFilterName: 't'
});
Vue.i18n.add(ufhy.LANG, ufhy.LANGS);
Vue.i18n.set(ufhy.LANG);