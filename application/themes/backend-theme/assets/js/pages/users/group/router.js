const Index = () => import(/* webpackChunkName: "users-group-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "users-group-form" */ './form.vue');
const Permissions = () => import(/* webpackChunkName: "users-group-permissions" */ './permissions.vue');

export default {
	path: 'group',
	name: 'users.group.index',
	meta: {
		title: 'menu::group',
		module: 'users/group',
		role: 'read',
		shortcut: ['search', 'refresh'],
	},
	component: Index,
	children: [
		{
			path: 'create',
			name: 'users.group.create',
			meta: {
				title: 'menu::group',
				module: 'users/group',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'users.group.edit',
			meta: {
				title: 'menu::group',
				module: 'users/group',
				role: 'edit'
			},
			component: Form
		},
		{
			path: 'permission/:id',
			name: 'users.group.permission',
			meta: {
				title: 'menu::group',
				module: 'users/group',
				role: 'change_permission'
			},
			component: Permissions
		},
	]
}