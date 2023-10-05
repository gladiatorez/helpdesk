<?php

class Module_References {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'References',
            ),
            'menu' => 'references',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--TagGroup',
            'sections' => array(
                'company' => array(
                    'name' => 'menu::company',
                    'uri' => 'references/company',
                    'roles' => [
                        'read', 'create', 'edit', 'remove'
                    ],
                ),
                'department' => array(
                    'name' => 'menu::department',
                    'uri' => 'references/department',
                    'roles' => [
                        'read', 'create', 'edit', 'remove'
                    ],
                ),
                'category' => array(
                    'name' => 'menu::category',
                    'uri' => 'references/category',
                    'roles' => [
                        'read', 'create', 'edit', 'remove'
                    ],
                ),
                'priority' => array(
                    'name' => 'menu::priority',
                    'uri' => 'references/priority',
                    'roles' => [
                        'read', //'create', 'edit', 'remove'
                    ],
                ),
                'reason' => array(
                    'name' => 'menu::reason',
                    'uri' => 'references/reason',
                    'roles' => [
                        'read', 'create', 'edit', 'remove'
                    ],
                ),
                'keyword' => array(
                    'name' => 'menu::keyword',
                    'uri' => 'references/keyword',
                    'roles' => [
                        'read', 'create', 'edit', 'remove'
                    ],
                ),
                'email_list' => array(
                    'name' => 'menu::email_list',
                    'uri' => 'references/email_list',
                    'roles' => [
                        'read', 'create', 'edit', 'remove'
                    ],
                ),
            )
        );
    }

    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]);

        if (userHasModule('references')) {
            if (isset($info['sections']) && is_array($info['sections'])) {
                $submenus = [];
                foreach ($info['sections'] as $key => $item) {
                    if (userHasModuleSection('references', $key)) {
                        $submenus[$item['name']] = new stdClass();
                        $submenus[$item['name']]->url = $item['uri'];
                        $submenus[$item['name']]->active = $key === $current_state;
                    }
                }

                $menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]['icon'] = $info['icon'];
                $menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]['items'] = $submenus;
            }
        }
    }
}