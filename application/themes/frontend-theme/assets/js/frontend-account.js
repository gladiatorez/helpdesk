import Vue from 'vue';
import VueRouter from 'vue-router';
import Logo from '../../../backend-theme/assets/js/components/core/Logo.vue';
import SearchButton from './components/SearchButton.vue';
import LoginButton from './components/LoginButton.vue';
import AccountDrawer from './components/AccountDrawer.vue';
import AccountMenu from './components/AccountMenu.vue';
import '../../../backend-theme/assets/js/plugins/socketio';

Vue.component('core-logo', Logo);
Vue.component('search-button', SearchButton);
Vue.component('login-button', LoginButton);
Vue.component('account-drawer', AccountDrawer);
Vue.component('account-menu', AccountMenu);

import Snackbars from '../../../backend-theme/assets/js/components/Snackbars';
Vue.use(Snackbars);

import api from './utils/api';
import moment from 'moment';
Vue.prototype.$axios = api();
Vue.prototype.$moment = moment;

const router = new VueRouter({
    routes: [
        { path: '/', component: () => import('./accountPage/information.vue') },
        { path: '/personalinfo', component: () => import('./accountPage/personalInfo.vue') },
        { 
            path: '/history', 
            component: () => import('./accountPage/history.vue') 
        },
        { 
            path: '/view/:id', 
            name: 'detailticket',
            component: () => import('./accountPage/detail-ticket.vue') 
        },
    ]
})

window.VUE = new Vue({
    el: "#root",
    router,
    vuetify,
    data: {
        isLoading: false,
        isMounted: false,
        navbarDark: false,
        navbarColor: 'white',
        navbarFlat: false
    },
    mounted() {
        this.$el.style.display = 'block';
        this.$nextTick(() => {
            this.isMounted = true;
        })
    },
    methods: {
        pagingChange(payload) {
            this.$emit('onPagingChange', payload);
        },
    }
});