<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Informer_Controller
 * 
 * @property The_auth_frontend $the_auth_frontend
 */
class Informer_Controller extends Public_Controller
{
    public $_themeName = 'frontend-theme';

    public function __construct()
    {
        parent::__construct();

        if (!frontendIsLoggedIn()) 
        {
            $this->session->set_flashdata('message.error', lang('msg::access_denied'));
            redirect();
        }

        $this->template->set_layout('account_layout');
    }
}