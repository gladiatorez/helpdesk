<?php

class Module_Tickets {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Tickets',
            ),
            'menu' => 'tickets',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--Ticket',
            'roles' => [
                'read', 'manage'
            ],
        );
    }

    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]);
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']] = new stdClass();
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->icon = $info['icon'];
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->url = 'tickets';
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->active = 'tickets' === $current_state;
    }
}