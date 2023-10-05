const Index = () => import(/* webpackChunkName: "reference-department-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "reference-department-form" */ './form.vue');

export default {
	path: 'department',
	name: 'references.department.index',
	meta: {
		title: 'menu::department',
		shortcut: ['search', 'refresh'],
		module: 'references/department',
		role: 'read'
	},
	component: Index,
	children: [
		{
			path: 'create',
			name: 'references.department.create',
			meta: {
				title: 'menu::department',
				titleSub: '',
				breadcrumb: true,
				breadcrumbText: '',
				module: 'references/department',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'references.department.edit',
			meta: {
				title: 'menu::department',
				titleSub: '',
				breadcrumb: true,
				breadcrumbText: '',
				module: 'references/department',
				role: 'edit'
			},
			component: Form
		},
	]
}