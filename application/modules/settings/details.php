<?php

class Module_Settings{

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Settings',
            ),
            'icon' => 'la la-sliders',
            'menu' => 'settings',
            'menu_group' => 'navigation',
            'roles' => array(
                'changes'
            ),
        );
    }

    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]);
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']] = new stdClass();
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->icon = $info['icon'];
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->url = 'settings';
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->active = 'settings' === $current_state;
    }

}