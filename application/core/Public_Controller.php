<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Public_Controller
 * 
 * @property The_auth_frontend $the_auth_frontend
 * @property MY_Pagination $pagination
 */
class Public_Controller extends MY_Controller
{
    public $_themeName = 'frontend-theme';
    public $_section = null;

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('frontend');

        $this->load->library(['auth/the_auth_frontend']);
        $this->_currentUserFrontend = ci()->_currentUserFrontend = $this->the_auth_frontend->getUserLogin();

        $ufhy['current_user_frontend'] = $this->_currentUserFrontend;
        $ufhy['section'] = $this->_section;
        $this->load->vars($ufhy);

        $this->template->set_layout('default_layout');
        $this->_loadThemeConfiguration();
        $this->_buildNavigation();
    }

    public function _buildNavigation()
    {
        $menus = [
            [
                'title' => lang('menu::frontend:home'),
                'url'   => site_url(),
                'active' => $this->_section === 'welcome'
            ],
            [
                'title' => lang('menu::frontend:faq'),
                'url' => site_url('faq'),
                'active' => $this->_section === 'faq'
            ],
            [
                'title' => lang('menu::frontend:request_ticket'),
                'url' => site_url('request-ticket'),
                'active' => $this->_section === 'request-ticket'
            ],
            [
                'title' => lang('menu::frontend:user_guide'),
                'url' => site_url(),
                'active' => $this->_section === 'user_guide'
            ]
        ];

        $this->template->menu_items = $menus;
    }
}