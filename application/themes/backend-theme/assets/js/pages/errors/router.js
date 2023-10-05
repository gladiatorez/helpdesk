const Permission = () => import(/* webpackChunkName: "error-permissions" */ './permission.vue');

const Component = {
  name: 'error-page',
  template: '<router-view></router-view>'
};

export default {
  path: '/error',
  name: 'error_page',
  component: Component,
  children: [
    {
      path: 'permission',
      name: 'error_page.permission.index',
      meta: {
        title: 'Error permission',
      },
      component: Permission,
    }
  ]
};