<?php

class Module_Dashboard {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Dashboard',
            ),
            'menu' => 'dashboard',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--ViewDashboard',
            'sections' => array(
                'general' => array(
                    'name' => 'menu::dashboard_general',
                    'uri' => 'dashboard/general',
                    'roles' => [],
                ),
                'dating' => array(
                    'name' => 'menu::dashboard_dating',
                    'uri' => 'dashboard/dating',
                    'roles' => [],
                )
            ),
//            'roles' => array(),
//            'shortcuts' => array()
        );
    }

    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]);

        if (userHasModule('dashboard')) {
            if (isset($info['sections']) && is_array($info['sections'])) {
                $submenus = [];
                foreach ($info['sections'] as $key => $item) {
                    if (userHasModuleSection('dashboard', $key)) {
                        $submenus[$item['name']] = new stdClass();
                        $submenus[$item['name']]->url = $item['uri'];
                        $submenus[$item['name']]->active = $key === $current_state;
                    }
                }

                $menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]['icon'] = $info['icon'];
                $menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]['items'] = $submenus;
            }
        }
//        unset($menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]);
//        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']] = new stdClass();
//        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->icon = $info['icon'];
//        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->url = 'dashboard';
//        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->active = 'dashboard' === $current_state;
    }
}