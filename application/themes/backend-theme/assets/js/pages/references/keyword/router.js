const Index = () => import(/* webpackChunkName: "reference-keyword-index" */ './index.vue');
// const Form = () => import('./form.vue');

export default {
	path: 'keyword',
	name: 'references.keyword.index',
	meta: {
		title: 'menu::keyword',
		module: 'references/keyword',
		role: 'read',
		shortcut: ['search', 'refresh'],
	},
	component: Index,
	/*children: [
		{
			path: 'create',
			name: 'references.keyword.create',
			meta: {
				title: 'menu::keyword',
				module: 'references/keyword',
				role: 'create'
			},
			component: Form
		},
		{
			path: 'edit/:id',
			name: 'references.keyword.edit',
			meta: {
				title: 'menu::keyword',
				module: 'references/keyword',
				role: 'edit'
			},
			component: Form
		},
	]*/
}