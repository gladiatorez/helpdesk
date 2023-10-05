const Index = () => import(/* webpackChunkName: "reference-category-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "reference-category-form" */ './form.vue');

export default {
	path: 'category',
	name: 'references.category.index',
	meta: {
		title: 'menu::category',
		module: 'references/category',
		role: 'read',
		shortcut: ['refresh'],
	},
	component: Index,
	children: [
		{
			path: 'create',
			name: 'references.category.create',
			meta: {
				title: 'menu::category',
				titleSub: '',
				breadcrumb: true,
				breadcrumbText: '',
				module: 'references/category',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'references.category.edit',
			meta: {
				title: 'menu::category',
				titleSub: '',
				breadcrumb: true,
				breadcrumbText: '',
				module: 'references/category',
				role: 'edit'
			},
			component: Form
		},
	]
}