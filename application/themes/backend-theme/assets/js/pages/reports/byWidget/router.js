const Index = () => import(/* webpackChunkName: "reports-bywidget-index" */ './index.vue');

export default {
	path: 'by_widget',
	name: 'reports.by_widget.index',
	meta: {
		title: 'menu::by_widget',
		module: 'reports/by_widget',
		role: 'read',
	},
	component: Index,
}