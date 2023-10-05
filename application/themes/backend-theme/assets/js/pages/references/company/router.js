const Index = () => import(/* webpackChunkName: "reference-company-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "reference-company-form" */ './form.vue');
const Branch = () => import(/* webpackChunkName: "reference-company-branch" */ './branch.vue');

export default {
	path: 'company',
	name: 'references.company.index',
	meta: {
		title: 'menu::company',
		module: 'references/company',
		shortcut: ['search', 'refresh'],
		role: 'read'
	},
	component: Index,
	children: [
		{
			path: 'branch/:id',
			name: 'references.company.branch',
			meta: {
				title: 'menu::company',
				module: 'references/company',
				role: 'read'
			},
			component: Branch
		},
		{
			path: 'create',
			name: 'references.company.create',
			meta: {
				title: 'menu::company',
				module: 'references/company',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'references.company.edit',
			meta: {
				title: 'menu::company',
				module: 'references/company',
				role: 'edit'
			},
			component: Form
		},
	]
}