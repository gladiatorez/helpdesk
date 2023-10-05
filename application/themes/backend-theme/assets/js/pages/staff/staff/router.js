const Index = () => import('./index.vue');
const Form = () => import('./form.vue');

export default {
    path: 'staff',
    name: 'staff.staff.index',
    meta: {
        title: 'menu::staff_directory',
        module: 'staff/staff',
        role: 'read',
        shortcut: ['refresh'],
    },
    component: Index,
    children: [
        {
            path: 'create',
            name: 'staff.staff.create',
            meta: {
                title: 'menu::staff_directory',
                module: 'staff/staff',
                role: 'create'
            },
            component: Form
        },
        {
            path: 'edit/:id',
            name: 'staff.staff.edit',
            meta: {
                title: 'menu::staff_directory',
                module: 'staff/staff',
                role: 'edit'
            },
            component: Form
        },
    ]
}