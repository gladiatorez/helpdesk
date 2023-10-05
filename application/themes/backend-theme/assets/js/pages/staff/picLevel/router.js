const Index = () => import('./index.vue');
const Form = () => import('./form.vue');

export default {
	path: 'pic_level',
	name: 'staff.pic_level.index',
	meta: {
		title: 'menu::pic_level',
		module: 'staff/pic_level',
		role: 'read',
		shortcut: ['search', 'refresh'],
	},
	component: Index,
	children: [
		{
			path: 'create',
			name: 'staff.pic_level.create',
			meta: {
				title: 'menu::pic_level',
				module: 'staff/pic_level',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'staff.pic_level.edit',
			meta: {
				title: 'menu::pic_level',
				module: 'staff/pic_level',
				role: 'edit'
			},
			component: Form
		},
	]
}