<?php

class Module_Faq {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'FAQ',
            ),
            'menu' => 'faq',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--SurveyQuestions',
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
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->url = 'faq';
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->active = 'faq' === $current_state;
    }
}