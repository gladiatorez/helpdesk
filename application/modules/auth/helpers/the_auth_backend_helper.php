<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('backendIsLoggedIn'))
{
	function backendIsLoggedIn() 
	{
		return ci()->the_auth_backend->loggedIn();
	}
}

if (!function_exists('isUserAdmin'))
{
	function isUserAdmin() 
	{
		return ci()->the_auth_backend->isUserAdmin();
	}
}

if (!function_exists('isUserHelpdesk'))
{
	function isUserHelpdesk($userId = null) 
	{
		return ci()->the_auth_backend->isUserHelpdek($userId);
	}
}

if (!function_exists('isUserHeadOffice')) 
{
    function isUserHeadOffice($userId)
    {
        $user = $this->getUserLogin($userId);
        if (!$user) {
            return false;
        }

        return (bool)$user->branch->is_head_office;
    }
}

if (!function_exists('getUserBranch'))
{
	function getUserBranch() 
	{
        $branchId = ci()->session->userdata('branch_id');
        if (!empty($branchId)) {
            return $branchId;
        }

        return null;
	}
}

if (!function_exists('userHasModule'))
{
    function userHasModule($module)
    {
		if (isUserAdmin()) {
			return true;
		}

        return isset(ci()->permissions[$module]);
    }
}

if (!function_exists('userHasModuleSection'))
{
    function userHasModuleSection($module, $section)
    {
		if (isUserAdmin()) {
			return true;
		}

        return isset(ci()->permissions[$module][$section]);
    }
}

if (!function_exists('userHasRole'))
{
	function userHasRole($role, $module, $section = null)
	{
		if (!ci()->currentUser) {
			return false;
		}

		if (isUserAdmin()) {
			return true;
        }

		if (empty($section)) {
            $permission = ci()->permissions[$module];
        } else {
            $permission = ci()->permissions[$module][$section];
        }

        if (!$permission) {
            return false;
        }

        if (in_array($role, $permission)) {
            return true;
        }

        return false;
	}
}

if (!function_exists('userHasRoleOrDie'))
{
	function userHasRoleOrDie($role, $module, $section = null, $redirectTo = '/', $message = null)
	{
        if (!ci()->currentUser) {
            return false;
        }

        if (isUserAdmin()) {
            return true;
        }

        if (ci()->input->is_ajax_request() && !userHasRole($role, $module, $section)) {
            ci()->output->set_status_header(401);
            die(json_encode([
                'success' => false,
                'authenticated' => false,
                'message' => ($message ? $message : lang('msg::access_denied'))
            ]));
            // return false;
        }
        else if (!userHasRole($role, $module, $section)) {
            ci()->session->set_flashdata('message.error', ($message ? $message : lang('msg::access_denied')) );
            redirect($redirectTo);
        }

        return true;
	}
}

if (!function_exists('getUsersHasRole'))
{
    function getUsersHasRole($role, $module, $section = null)
    {
        ci()->load->model('users/group_permission_model');
        $groupPermissions = ci()->group_permission_model->get_all(['module' => $module]);
        
        if (!$groupPermissions) {
            return false;
        }

        $groupIds = [];
        foreach ($groupPermissions as $groupPermission) {
            $roles = null;
            if (!empty($groupPermission->roles)) {
                $roles = json_decode($groupPermission->roles);
            }

            $found = false;
            if ($section && isset($roles->{$section})) {
                $roleFilter = array_filter($roles->{$section}, function($value) use ($role) {
                    return $value === $role;
                });
                if (count($roleFilter) > 0) {
                    $found = true;
                }
            } else {
                $roleFilter = array_filter($roles->{$section}, function($value) use ($section) {
                    return $value === $role;
                });
                if (count($roleFilter) > 0) {
                    $found = true;
                }
            }

            if ($found) {
                array_push($groupIds, $groupPermission->group_id);
            }
        }

        if (!$groupIds) {
            return false;
        }

        $users = ci()->user_model->fields('id,username,email,branch_id')
            ->where('group_id', $groupIds)->get_all(['active' => '1']);
        
        return $users;
    }
}