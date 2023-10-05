import company from './company/router';
import department from './department/router';
import reason from './reason/router';
import category from './category/router';
import priority from './priority/router';
import keyword from './keyword/router';
import emailList from './emailList/router';

const Component = {
	name: 'references-page',
	template: '<router-view></router-view>'
};

export default {
	path: '/references',
	redirect: '/dashboard',
	meta: {
		title: 'References'
	},
	component: Component,
	children: [
		company, department, reason, category, priority, keyword, emailList
	]
}