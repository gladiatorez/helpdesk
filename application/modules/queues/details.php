<?php

class Module_Queues {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Queues',
            ),
            'menu' => 'queues',
            'menu_group' => 'navigation',
            'icon' => 'ms-Icon ms-Icon--Ascending',
            // 'roles' => array('read'),
            'sections' => array(
                'assignment' => array(
                    'name' => 'menu::assignment',
                    'uri' => 'queues/assignment',
                    'roles' => [
                        'read','close_ticket'
                    ],
                ),
                'task' => array(
                    'name' => 'Task',
                    'uri' => 'queues/task',
                    'roles' => [
                        'read'
                    ],
                ),
                'personil' => array(
                    'name' => 'menu::personil',
                    'uri' => 'queues/personil',
                    'roles' => [
                        'read'
                    ],
                ),
                
            )
        );
    }

    /*public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]);
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']] = new stdClass();
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->icon = $info['icon'];
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->url = 'queues';
        $menu['menu::group:'.$info['menu_group']]['menu::'.$info['menu']]->active = 'queues' === $current_state;
    }*/
    
    public function admin_menu(&$menu, $current_state)
    {
        $info = $this->info();
        unset($menu['menu::group:' . $info['menu_group']]['menu::' . $info['menu']]);

        if (userHasModule('queues')) {
            if (isset($info['sections']) && is_array($info['sections'])) {
                $submenus = [];
                foreach ($info['sections'] as $key => $item) {
                    if (userHasModuleSection('queues', $key)) {
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