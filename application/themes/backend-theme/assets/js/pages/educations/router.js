const Form = () => import(/* webpackChunkName: "educations-form" */ './form.vue');
const Index = () => import(/* webpackChunkName: "educations-index" */ './index.vue');

const Component = {
	name: 'educations-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/educations',
	meta: {
		title: 'menu::educations',
		shortcut: ['search', 'refresh'],
		module: 'educations',
		role: 'read'
	},
	component: Component,
	children: [
		{
			path: '/',
			meta: {
				title: 'menu::educations',
				module: 'educations',
				role: 'read',
				shortcut: ['search', 'refresh'],
			},
			name: 'educations.index',
			component: Index,
		},
		{
			path: 'create',
			meta: {
				title: 'educations::heading_new',
				module: 'educations',
				role: 'create',
				shortcut: ['back', 'saveClose'],
			},
			name: 'educations.create',
			component: Form,
		},
	]
}