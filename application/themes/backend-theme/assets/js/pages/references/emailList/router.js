const Index = () => import(/* webpackChunkName: "reference-emaillist-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "reference-emaillist-form" */ './form.vue');

export default {
	path: 'email_list',
	name: 'references.email_list.index',
	meta: {
		title: 'menu::email_list',
		module: 'references/email_list',
		role: 'read',
		shortcut: ['search', 'refresh'],
	},
	component: Index,
	children: [
		{
			path: 'create',
			name: 'references.email_list.create',
			meta: {
				title: 'menu::email_list',
				module: 'references/email_list',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'references.email_list.edit',
			meta: {
				title: 'menu::email_list',
				module: 'references/email_list',
				role: 'edit'
			},
			component: Form
		},
	]
}