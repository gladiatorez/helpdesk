<?php

class Module_Educations {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Education images',
            ),
            'menu' => 'educations',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--FileImage',
            'roles' => [
                'read', 'create', 'edit', 'remove'
            ],
        );
    }

    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]);
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']] = new stdClass();
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->icon = $info['icon'];
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->url = 'educations';
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->active = 'educations' === $current_state;
    }
}