const Form = () => import(/* webpackChunkName: "faq-form" */ './form.vue');
const Index = () => import(/* webpackChunkName: "faq-index" */ './index.vue');

const Component = {
	name: 'faq-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/faq',
	meta: {
		title: 'menu::faq',
		shortcut: ['search', 'refresh'],
		module: 'faq',
		role: 'read'
	},
	component: Component,
	children: [
		{
			path: '/',
			meta: {
				title: 'menu::faq',
				module: 'faq',
				role: 'read',
				shortcut: ['search', 'refresh'],
			},
			name: 'faq.index',
			component: Index,
		},
		{
			path: 'create',
			meta: {
				title: 'faq::heading_new',
				module: 'faq',
				role: 'create',
				shortcut: ['back', 'saveClose'],
			},
			name: 'faq.create',
			component: Form,
		},
		{
			path: 'edit/:id',
			meta: {
				title: 'faq::heading_edit',
				module: 'faq',
				role: 'edit',
				shortcut: ['back', 'saveClose'],
			},
			name: 'faq.edit',
			component: Form,
		}
	]
}