const Index = () => import(/* webpackChunkName: "settings-index" */ './index.vue');

export default {
	path: '/settings',
	meta: {
		title: 'Settings',
	},
	name: 'settings.index',
	component: Index,
}