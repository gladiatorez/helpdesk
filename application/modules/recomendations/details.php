<?php

class Module_Recomendations {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Recomendations',
            ),
            'menu' => 'recomendations',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--UpgradeAnalysis',
            'roles' => [
                'read', 'create', 'edit', 'remove', 'approve'
            ],
        );
    }

    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]);
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']] = new stdClass();
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->icon = $info['icon'];
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->url = 'recomendations';
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->active = 'recomendations' === $current_state;
    }
}