<?php

class Module_Search {

    public $version = '1.1.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Search',
            ),
            'menu' => 'search',
            'menu_group' => '',
            'icon' => 'ms-Icon--search2',
            'roles' => [],
        );
    }
}