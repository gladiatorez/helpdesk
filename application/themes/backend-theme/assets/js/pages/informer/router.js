const Index = () => import(/* webpackChunkName: "informer-index" */ './index.vue');
const Form = () => import(/* webpackChunkName: "informer-form" */ './form.vue');


export default {
	path: '/informer',
	meta: {
		title: 'menu::informer',
		shortcut: ['search', 'refresh'],
		module: 'informer',
		role: 'read'
	},
	name: 'informer.index',
	component: Index,
	children: [
		{
            path: 'create',
            name: 'informer.create',
            meta: {
                title: 'menu::informer',
                module: 'informer',
                role: 'create'
            },
            component: Form
        },
        {
            path: 'edit/:id',
            name: 'informer.edit',
            meta: {
                title: 'menu::informer',
                module: 'informer',
                role: 'edit'
            },
            component: Form
        },
	]
}