const Index = () => import(/* webpackChunkName: "queues-personil-index" */ './index.vue');
const Detail = () => import(/* webpackChunkName: "queues-personil-detail" */ './detail.vue');

const Component = {
	name: 'queues-personil-page',
	template: '<router-view></router-view>'
};

export default {
	path: 'personil',
	meta: {
		title: 'menu::queues',
		shortcut: ['search', 'refresh'],
		module: 'queues/personil',
		role: 'read'
	},
	component: Component,
	children: [
		{
			path: '/',
			meta: {
				title: 'Your staff',
				module: 'queues/personil',
				role: 'read',
				shortcut: ['search', 'refresh'],
			},
			name: 'queues.personil.index',
			component: Index,
		},
		{
			path: 'view/:id',
			meta: {
				title: 'Your staff',
				module: 'queues/personil',
				role: 'read',
				shortcut: ['back'],
			},
			name: 'queues.personil.detail',
			component: Detail,
		},
	]
	
}