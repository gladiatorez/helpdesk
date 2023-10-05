const Index = () => import(/* webpackChunkName: "dashboard-index" */ './index.vue');
const TicketDetail = () => import(/* webpackChunkName: "dashboard-ticket-detail" */ './ticketDetail.vue');
const Dating = () => import(/* webpackChunkName: "dashboard-dating" */ './dating.vue');

const Component = {
	name: 'dashboard-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/dashboard',
	meta: {
		title: 'Dashboard',
	},
	redirect: '/dashboard/general',
	component: Component,
	children: [
		{
			path: 'general',
			meta: {
				title: 'General dashboard',
				module: 'dashboard',
				role: 'read',
			},
			name: 'dashboard.index',
			component: Index,
			children: [
				{
					path: 'ticketDetail/:id',
					name: 'dashboard.ticketDetail',
					meta: {
						title: 'General dashboard',
						module: 'dashboard',
						role: 'read',
					},
					component: TicketDetail
				},
			]
		},
		{
			path: 'dating',
			name: 'dashboard.dating',
			meta: {
				title: 'SLA dashboard',
				module: 'dashboard',
				role: 'read',
			},
			component: Dating
		}
	]
};