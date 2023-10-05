const Index = () => import(/* webpackChunkName: "reference-priority-index" */ './index.vue');
// const Form = () => import('./form.vue');

export default {
	path: 'priority',
	name: 'references.priority.index',
	meta: {
		title: 'menu::priority',
		module: 'references/priority',
		role: 'read',
		shortcut: ['search', 'refresh'],
	},
	component: Index,
	// children: [
	// 	{
	// 		path: 'create',
	// 		name: 'references.priority.create',
	// 		meta: {
	// 			title: 'menu::priority',
	// 			module: 'references/priority',
	// 			role: 'create'
	// 		},
	// 		component: Form
	// 	},
	// 	{
	// 		path: 'edit/:id',
	// 		name: 'references.priority.edit',
	// 		meta: {
	// 			title: 'menu::priority',
	// 			module: 'references/priority',
	// 			role: 'edit'
	// 		},
	// 		component: Form
	// 	},
	// ]
}