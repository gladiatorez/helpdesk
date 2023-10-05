const Form = () => import(/* webpackChunkName: "recomendations-form" */ './form.vue');
const Index = () => import(/* webpackChunkName: "recomendations-index" */ './index.vue');
const View = () => import(/* webpackChunkName: "recomendations-view" */ './view.vue');
const PhotoForm = () => import(/* webpackChunkName: "recomendations-view" */ './photoForm.vue');

const Component = {
    name: 'recomendations-page',
    template: '<router-view></router-view>'
};

export default {
    path: '/recomendations',
    meta: {
        title: 'menu::recomendations',
        shortcut: ['search', 'refresh'],
        module: 'recomendations',
        role: 'read'
    },
    component: Component,
    children: [
        {
            path: '/',
            meta: {
                title: 'menu::recomendations',
                module: 'recomendations',
                role: 'read',
                shortcut: ['search', 'refresh'],
            },
            name: 'recomendations.index',
            component: Index,
        },
        {
            path: 'create',
            meta: {
                title: 'recomendations::heading_new',
                module: 'recomendations',
                role: 'create',
                shortcut: ['back', 'saveClose'],
            },
            name: 'recomendations.create',
            component: Form,
        },
        {
            path: 'edit/:id',
            meta: {
                title: 'recomendations::heading_edit',
                module: 'recomendations',
                role: 'edit',
                shortcut: ['back', 'saveClose'],
            },
            name: 'recomendations.edit',
            component: Form,
        },
        {
            path: 'view/:id',
            meta: {
                title: 'recomendations::heading_detail',
                module: 'recomendations',
                role: 'read',
                shortcut: ['back','print'],
            },
            name: 'recomendations.view',
            component: View,
        },
        {
            path: 'attachPhoto/:id',
            meta: {
                title: 'recomendations::heading_attach_photo',
                module: 'recomendations',
                role: 'read',
                shortcut: ['back','saveClose'],
            },
            name: 'recomendations.attachPhoto',
            component: PhotoForm,
        }
    ]
}