const Index = () => import(/* webpackChunkName: "queues-assignment-index" */ './index.vue');
const Detail = () => import(/* webpackChunkName: "queues-assignment-detail" */ './detail.vue');

const Component = {
	name: 'queues-assignment-page',
	template: '<router-view></router-view>'
};

export default {
	path: 'assignment',
	meta: {
		title: 'menu::queues',
		shortcut: ['search', 'refresh'],
		module: 'queues/assignment',
		role: 'read'
	},
	component: Component,
	children: [
		{
			path: '/',
			meta: {
				title: 'Assignment',
				module: 'queues/assignment',
				role: 'read',
				shortcut: ['search', 'refresh'],
			},
			name: 'queues.assignment.index',
			component: Index,
		},
		{
			path: 'view/:id',
			meta: {
				title: 'Assignment detail',
				module: 'queues/assignment',
				role: 'read',
				shortcut: ['back'],
			},
			name: 'queues.assignment.detail',
			component: Detail,
		},
	]
	
}