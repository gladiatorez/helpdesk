const Index = () => import(/* webpackChunkName: "reference-reason-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "reference-reason-form" */ './form.vue');

export default {
	path: 'reason',
	name: 'references.reason.index',
	meta: {
		title: 'menu::reason',
		module: 'references/reason',
		role: 'read',
		shortcut: ['search', 'refresh'],
	},
	component: Index,
	children: [
		{
			path: 'create',
			name: 'references.reason.create',
			meta: {
				title: 'menu::reason',
				module: 'references/reason',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'references.reason.edit',
			meta: {
				title: 'menu::reason',
				module: 'references/reason',
				role: 'edit'
			},
			component: Form
		},
	]
}