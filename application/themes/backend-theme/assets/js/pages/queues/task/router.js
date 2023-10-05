const Index = () => import(/* webpackChunkName: "queues-task-index" */ './index.vue');
const Detail = () => import(/* webpackChunkName: "queues-task-detail" */ './detail.vue');
const Create = () => import(/* webpackChunkName: "queues-task-create" */ './create.vue');

const Component = {
	name: 'queues-task-page',
	template: '<router-view></router-view>'
};

export default {
	path: 'task',
	meta: {
		title: 'menu::queues',
		shortcut: ['search', 'refresh'],
		module: 'queues/task',
		role: 'read'
	},
	component: Component,
	children: [
		{
			path: '/',
			meta: {
				title: 'Task',
				module: 'queues/task',
				role: 'read',
				shortcut: ['search', 'refresh'],
			},
			name: 'queues.task.index',
			component: Index,
		},
		
		{
			path: 'view/:id',
			meta: {
				title: 'Task detail',
				module: 'queues/task',
				role: 'read',
				shortcut: ['back'],
			},
			name: 'queues.task.detail',
			component: Detail,
		},

		{
			path: 'create',
			meta: {
				title: 'Task New',
				module: 'queues/task',
				role: 'read',
				shortcut: ['back'],
			},
			name: 'queues.task.create',
			component: Create,
		},
	]
	
}