<?php

class Module_Staff {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Staff',
            ),
            'menu' => 'staff',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--PeopleAlert',
            'sections' => array(
                'staff' => array(
                    'name' => 'menu::staff_directory',
                    'uri' => 'staff/staff',
                    'roles' => [
                        'read', 'create', 'edit', 'remove'
                    ],
                ),
                'pic_level' => array(
                    'name' => 'menu::pic_level',
                    'uri' => 'staff/pic_level',
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

        if (userHasModule('staff')) {
            if (isset($info['sections']) && is_array($info['sections'])) {
                $submenus = [];
                foreach ($info['sections'] as $key => $item) {
                    if (userHasModuleSection('staff', $key)) {
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