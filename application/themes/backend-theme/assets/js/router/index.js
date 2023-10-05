import Vue from 'vue';
import Router from 'vue-router';
import NProgress from 'nprogress/nprogress';
import 'nprogress/nprogress.css';
import store from '../store';
import api from '../utils/api';

import paths from './paths';
import ability from '../utils/ability.js';

Vue.use(Router);

const loadI18n = async (module) => {
  const isLoaded = await store.dispatch('localisation/isLoaded', module);
  if (isLoaded) {
    return false;
  }

  return api().get('addons/i18n_by_module', {
    params: {
      module: module
    }
  }).then(response => {
    const { data } = response;
    Vue.i18n.add(ufhy.LANG, data);
    store.dispatch('localisation/addI18n', module);
  })
}


const beforeEach = async (to, from, next) => {
  NProgress.start();
  if (to.name === 'error_page.permission.index') {
    next();
  }
  else {
    if (!ability.can(to.meta.role, to.meta.module)) {
      next({
        path: '/error/permission'
      });
    } else {
      await loadI18n(to.meta.module)
      next()
    }
  }
};

const afterEach = (to, from) => {
  document.title = VUE.$t(to.meta.title) + ' - ' + SITE_TITLE_FULL;

  NProgress.done();
};

const router = new Router({
  base: '/',
  mode: 'hash',
  routes: paths,
});
router.beforeEach(beforeEach);
router.afterEach(afterEach);

export default router;
