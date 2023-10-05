<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class Backend_Controller
 *
 * @property The_auth_backend $the_auth_backend
 * @property The_file $the_file
 * @property Group_permission_model $group_permission_model
 * @property Module_model $module_model
 */
class Backend_Controller extends MY_Controller
{
    public $_themeName = 'backend-theme';
    public $_section = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model(['addons/module_model']);
        $this->load->library(['auth/the_auth_backend']);

        $this->_currentUser = ci()->currentUser = $this->the_auth_backend->getUserLogin();
        $this->_permissions = ci()->permissions = $this->_currentUser
            ? $this->group_permission_model->getByGroup($this->_currentUser->group_id)
            : [];
        ci()->enabledModules = $this->module_model->getAll(['status' => 'A', 'order' => true], true);

        if (!$this->_checkAccess()) {
            $this->session->set_flashdata('message.error', lang('msg::access_denied'));
            redirect(BACKEND_URLPREFIX);
        }

        $this->load->library('events');

        $this->_moduleDetails = ci()->moduleDetails = false;
        foreach (ci()->enabledModules as $module) {
            if (strtoupper($module['slug']) === strtoupper($this->_module)) {
                $this->_moduleDetails = ci()->moduleDetails = $module;
                continue;
            }
        }

        $this->_loadThemeConfiguration();

        $this->load->library('events');

        $ufhy['module_details'] = $this->_moduleDetails;
        $ufhy['current_user'] = $this->_currentUser;
        $ufhy['permissions'] = $this->_permissions;
        $ufhy['isAdmin'] = $this->the_auth_backend->isUserAdmin();
        $this->load->vars($ufhy);

        $this->template->active_section = $this->_section;
        $this->_buildNavigation();

        // set default language already loaded
        ci()->template->append_metadata(
            sprintf(
                '<script>ufhy.LANG = "%s"; ufhy.LANGS = %s;</script>',
                CURRENT_LANGUAGE,
                json_encode($this->lang->language)
            )
        );
    }

    private function _checkAccess()
    {
        $defaultPages = [
            'acp/index',
            'acp/dashboard',
            'acp/profile',
        ];
        $currentPage = $this->uri->segment(1, '') . '/' . $this->uri->segment(2, 'index');
        
        if ($currentPage === BACKEND_URLPREFIX . '/api') {
            // die($this->uri->uri_string());
            /* if (in_array($this->uri->uri_string(), [
                'acp/api/addons/i18n', 'acp/api/addons/i18n_by_module'
            ])) { */
                return true;
            // }
        }

        $ignorePages = [
            'acp/auth',
            'acp/auth/login',
            'acp/auth/logout',
            'acp/auth/test',
            'acp/api/addons/i18n',
        ];
        if (in_array($currentPage, $ignorePages)) {
            return true;
        }

        if (!$this->_currentUser) {
            $this->session->set_flashdata('message.error', lang('msg::must_login'));
            $redirectKey = $this->config->item('back_auth_redirect_key');
            $this->session->set_userdata($redirectKey, $this->uri->uri_string());
            redirect('acp/auth');
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
                }
                else {
                    $filter = array_filter($permissionSection, function($value) {
                        return $value === 'read';
                    });
                    return count($filter) > 0;
                }
            }
        }

        return false;
    }

    public function _buildNavigation()
    {
        $menuItems = [];
        $orderedMenu = [];
        foreach (ci()->enabledModules as $module)
        {
            if ( $module['menu'] && (isset($this->permissions[$module['slug']]) OR $this->the_auth_backend->isUserAdmin()) )
            {
                $menuItems['menu::group:'.$module['menu_group']]
                ['menu::'.$module['menu']]
                ['menu::'.$module['slug']] = new stdClass();
                $menuItems['menu::group:'.$module['menu_group']]
                ['menu::'.$module['menu']]
                ['menu::'.$module['slug']]->url = $module['slug'];
                $menuItems['menu::group:'.$module['menu_group']]
                ['menu::'.$module['menu']]
                ['menu::'.$module['slug']]->active = $this->_module === $module['slug'];
            }

            if (method_exists($module['module'], 'admin_menu')) {
                $module['module']->admin_menu($menuItems, $this->_section ? $this->_section : $this->_module);
            }
        }

        if ($menuItems)
        {
            $translatedMenuItems = array();

            // translate any additional top level menu keys so the array_merge works
            foreach ($menuItems as $key => $menuItem)
            {
                $translatedMenuItems[$key] = $menuItem;
            }

            $orderedMenu = array_merge_recursive($orderedMenu, $translatedMenuItems);
        }

        $this->template->menu_items = $orderedMenu;
        if ($orderedMenu) {
            ci()->template->append_metadata(
                sprintf(
                    '<script>window.MENU_ITEMS = %s;</script>',
                    json_encode($orderedMenu['menu::group:navigation'])
                )
            );
        }
    }
}