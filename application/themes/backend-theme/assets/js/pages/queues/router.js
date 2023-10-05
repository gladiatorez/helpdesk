import assignment from './assignment/router';
import personil from './personil/router';
import task from './task/router';

const Component = {
	name: 'queues-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/queues',
	redirect: '/dashboard',
	meta: {
		title: 'Queues'
	},
	component: Component,
	children: [
		assignment, personil, task
	]
}