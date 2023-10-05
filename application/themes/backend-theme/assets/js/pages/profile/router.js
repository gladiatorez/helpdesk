const Index = () => import(/* webpackChunkName: "profile-index" */ './index.vue');

export default {
	path: '/profile',
	meta: {
		title: 'Profile',
		module: 'profile',
		role: 'read',
	},
	name: 'profile.index',
	component: Index,
}