<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Backend_Api_Controller extends Backend_Controller
{
    public $_section = null;

    public function __construct()
    {
        parent::__construct();

        if (!$this->_checkAccess()) {
            $this->output->set_status_header('405');
            $this->output->set_header('Content-Type: application/json; charset=utf-8');
            die(json_encode([
                'success' => false,
                'message' => lang('msg::access_denied') . ' Refresh browser anda'
            ]));
        }

        $this->output->enable_profiler(FALSE);
    }

    private function _checkAccess()
    {
        $defaultPages = [
            'acp/api/index',
            'acp/api/dashboard',
            'acp/api/profile',
            'acp/api/notifications',
            'acp/api/addons',
        ];

        $currentPage = $this->uri->segment(1, '') . '/' . $this->uri->segment(2, '') . '/' . $this->uri->segment(3, 'index');
        
        if (!$this->_currentUser) {
            $this->output->set_status_header('403');
            $this->output->set_header('Content-Type: application/json; charset=utf-8');
            die(json_encode([
                'success' => false,
                'message' => lang('msg::must_login') . ' Refresh browser anda'
            ]));
        }

        if ($this->the_auth_backend->isUserAdmin()) {
            return true;
        }

        if ($this->_currentUser) {
            if (in_array($currentPage, $defaultPages) && $this->_permissions) {
                return true;
            }

            if (array_key_exists($this->_module, $this->_permissions)) {
                $permissionSection = $this->_permissions[$this->_module];
                if (is_multi_array($permissionSection)) {
                    return array_key_exists($this->_section, $permissionSection);
                } else {
                    $filter = array_filter($permissionSection, function ($value) {
                        return $value === 'read';
                    });
                    return count($filter) > 0;
                }
            }
        }

        return false;
    }
}