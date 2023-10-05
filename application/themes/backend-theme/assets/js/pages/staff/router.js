import picLevel from './picLevel/router';
import staff from './staff/router';

const Component = {
	name: 'staff-router-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/staff',
	redirect: '/dashboard',
	meta: {
		title: 'Staff'
	},
	component: Component,
	children: [
		picLevel, staff
	]
}