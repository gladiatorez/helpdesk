import Vue from 'vue';

import CoreLogo from '../../../backend-theme/assets/js/components/core/Logo.vue';
Vue.component('core-logo', CoreLogo);

window.VUE = new Vue({
    el: "#root",
    vuetify,
    data: {
        rememberMe: false,
    },
    mounted() {
        this.$el.style.display = 'block';
    },
    methods: {
        doLogin() {
            this.$refs.rememberMe.value = this.rememberMe ? '1' : '0';
            this.$refs.formLogin.submit();
        }
    }
});