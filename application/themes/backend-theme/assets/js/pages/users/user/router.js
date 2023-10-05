const Index = () => import(/* webpackChunkName: "users-user-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "users-user-form" */ './form.vue');

export default {
	path: 'user',
	name: 'users.user.index',
	meta: {
		title: 'menu::user',
		module: 'users/user',
		role: 'read',
		shortcut: ['search', 'refresh'],
	},
	component: Index,
	children: [
		{
			path: 'create',
			name: 'users.user.create',
			meta: {
				title: 'menu::user',
				module: 'users/user',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'users.user.edit',
			meta: {
				title: 'menu::user',
				module: 'users/user',
				role: 'edit'
			},
			component: Form
		},
	]
}