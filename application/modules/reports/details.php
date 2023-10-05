<?php

class Module_Reports {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Reports',
            ),
            'menu' => 'reports',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--ReportLibrary',
            'sections' => array(
                'general' => array(
                    'name' => 'menu::general',
                    'uri' => 'reports/general',
                    'roles' => [
                        'read'
                    ],
                ),
                'by_widget' => array(
                    'name' => 'menu::by_widget',
                    'uri' => 'reports/by_widget',
                    'roles' => [
                        'read'
                    ],
                ),
            )
        );
    }

    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]);

        if (userHasModule('reports')) {
            if (isset($info['sections']) && is_array($info['sections'])) {
                $submenus = [];
                foreach ($info['sections'] as $key => $item) {
                    if (userHasModuleSection('reports', $key)) {
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