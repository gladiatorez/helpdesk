const Index = () => import(/* webpackChunkName: "reports-general-index" */ './index.vue');

export default {
	path: 'general',
	meta: {
		title: 'menu::general',
		shortcut: ['refresh'],
		module: 'reports/general',
		role: 'read',
	},
	name: 'reports.general.index',
	component: Index,
}