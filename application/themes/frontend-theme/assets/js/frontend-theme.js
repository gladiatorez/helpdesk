import Vue from 'vue';

import Logo from '../../../backend-theme/assets/js/components/core/Logo.vue';
import HeroParticle from './components/HeroParticle.vue';
import FaqCategoriesDrawer from './components/FaqCategoriesDrawer.vue';
import FaqCategoriesMenu from './components/FaqCategoriesMenu.vue';
import SearchButton from './components/SearchButton.vue';
import LoginButton from './components/LoginButton.vue';
import RateForm from './components/RateForm.vue';
import WhatsAppBtn from "./components/WhatsAppBtn.vue";
import TelegramBtn from "./components/TelegramBtn.vue";



Vue.component('core-logo', Logo);
Vue.component('core-logo', Logo);
Vue.component('hero-particle', HeroParticle);
Vue.component('faq-categories-drawer', FaqCategoriesDrawer);
Vue.component('faq-categories-menu', FaqCategoriesMenu);
Vue.component('search-button', SearchButton);
Vue.component('login-button', LoginButton);
Vue.component('rate-form', RateForm);
Vue.component('whats-app-btn', WhatsAppBtn);
Vue.component('telegram-btn', TelegramBtn);

import CoreSnackbars from '../../../backend-theme/assets/js/components/Snackbars';
Vue.use(CoreSnackbars);

import '../styles/frontend-theme.scss';

import api from './utils/api';
Vue.prototype.$axios = api();

window.VUE = new Vue({
	el: "#root",
	vuetify,
	data: {
		isLoading: false,
		isMounted: false,
		navbarDark: false,
		navbarColor: 'white',
		navbarFlat: false
	},
	created() {
		// this.$vuetify.theme.primary = '#5867dd';
		this.$vuetify.theme.success = '#34bfa3';
		this.$vuetify.theme.warning = '#ffb822';
		this.$vuetify.theme.error = '#f4516c';

		this.$vuetify.icons.menu = 'ms-Icon--menu';
		this.$vuetify.icons.next = 'la-angle-right';
		this.$vuetify.icons.prev = 'la-angle-left';
		this.$vuetify.icons.dropdown = 'la-angle-down';
		this.$vuetify.icons.sort = 'la-arrow-up';
		this.$vuetify.icons.expand = 'la-chevron-circle-down';
		this.$vuetify.icons.checkboxIndeterminate = "la-minus-square";
		this.$vuetify.icons.checkboxOff = "la-square-o";
		this.$vuetify.icons.checkboxOn = "la-check-square";
		this.$vuetify.icons.error = "la-warning";
		this.$vuetify.icons.success = "la-check-circle";
		this.$vuetify.icons.cancel = "la-times-circle";
		this.$vuetify.icons.close = "la-times-circle";
		this.$vuetify.icons.clear = "la-times";
		this.$vuetify.icons.warning = "la-exclamation-triangle";
		this.$vuetify.icons.radioOff = "la-circle";
		this.$vuetify.icons.radioOn = "la-check-circle";
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