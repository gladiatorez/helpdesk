const Index = () => import(/* webpackChunkName: "tickets-index" */ './index.vue');
const Detail = () => import(/* webpackChunkName: "tickets-detail" */ './detail.vue');
const Create = () => import(/* webpackChunkName: "tickets-create" */ './create.vue');

const Component = {
	name: 'tickets-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/tickets/all',
	meta: {
		title: 'menu::tickets',
		shortcut: ['search', 'refresh', 'create'],
		module: 'tickets',
		role: 'read'
	},
	component: Component,
	children: [
		{
			path: '/',
			meta: {
				title: 'menu::tickets',
				module: 'tickets',
				role: 'read',
				shortcut: ['search', 'refresh'],
			},
			name: 'tickets.list.index',
			component: Index,
		},
		{
			path: 'view/:id',
			meta: {
				title: 'Detail ticket',
				module: 'tickets',
				role: 'read',
				shortcut: ['back'],
			},
			name: 'tickets.list.detail',
			component: Detail,
		},
		{
			path: 'create',
			meta: {
				title: 'tickets::heading_new',
				module: 'tickets',
				role: 'read',
				shortcut: ['back', 'saveClose'],
			},
			name: 'tickets.create',
			component: Create,
		},
	]
	
}