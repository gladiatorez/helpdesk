import byWidget from './byWidget/router';
import general from './general/router';

const Component = {
	name: 'reports-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/reports',
	redirect: '/dashboard',
	meta: {
		title: 'Reports'
	},
	component: Component,
	children: [
		byWidget, general
	]
}