<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Informer_Controller
 * 
 * @property The_auth_frontend $the_auth_frontend
 */
class Informer_Api_Controller extends Public_Controller
{
    public $_themeName = null;

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('profile/profile');

        if (!frontendIsLoggedIn()) {
            $this->output->set_status_header('405');
            $this->output->set_header('Content-Type: application/json; charset=utf-8');
            die(json_encode([
                'success' => false,
                'message' => lang('msg::access_denied') . ' Refresh browser anda'
            ]));
        }

        $this->output->enable_profiler(false);
    }
}