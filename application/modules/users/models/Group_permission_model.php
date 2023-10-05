<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Group_permission_model
 *
 * @property Group_model $group_model
 * @property Module_model $module_model
 */
class Group_permission_model extends MY_Model
{
    public $table = 'user_groups_permissions';
    public $primary_key = 'id';
    public $fillable = [
        'group_id','module','roles','created_by','updated_by'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function all()
    {

    }

    public function getByGroup($groupId)
    {
        $this->load->model('users/group_model');
        $group = $this->group_model->get(['id' => $groupId]);
        if (!$group) {
            return [];
        }

        if ($group->is_admin) {
            $permissions = $this->module_model->getAllPermissions();
            $result = [];
            foreach ($permissions as $permission) {
                $mod = new stdClass();
                $mod->module = $permission['slug'];

                $roles = null;
                if (!empty($permission['roles'])) {
                    $roles = array_map(function ($role) {
                        return $role['value'];
                    }, $permission['roles']);
                } else {
                    if (!empty($permission['sections'])) {
                        foreach ($permission['sections'] as $section) {
                            if (!empty($section['roles'])) {
                                $roles[$section['slug']] = array_map(function ($role) {
                                    return $role['value'];
                                }, $section['roles']);
                            }
                        }
                    }
                }
                $mod->roles = json_encode($roles);

                $result[] = $mod;
            }
        }
        else {
            $result = $this->get_all(['group_id' => $groupId]);
        }

        $rules = array();
        if (!$result) {
            return $rules;
        }

        foreach ($result as $row) {
            $rules[$row->module] = $row->roles
                ? json_decode($row->roles, true)
                : true;
        }

        return $rules;
    }

    public function save($groupId, $modules, $permissions)
    {
        $this->_database->trans_start();

        // clear roles by group id
        $this->force_delete(['group_id' => $groupId]);

        $this->before_create[] = 'add_creator';
        $this->before_create[] = 'add_updater';

        if (!isset($modules['dashboard'])) {
            $module['dashboard'] = 'dashboard';
        }

        if (!isset($permissions['dashboard'])) {
            $permissions['dashboard'] = [
                'roles' => [
                    'read' => 'read'
                ]
            ];
        }

        $modulePermissions = [];
        if (!empty($permissions) && is_array($permissions)) 
        {
            foreach ($permissions as $permissionKey => $permission) 
            {
                if (!array_key_exists($permissionKey, $modulePermissions)) {
                    array_push($modulePermissions, $permissionKey);
                }

                $data = [ 'module' => $permissionKey, 'group_id' => $groupId ];
                if ( isset($permission['roles']) ) 
                {
                    $data['roles'] = json_encode(array_keys($permission['roles']));
                } 
                else if ( isset($permission['sections']) ) 
                {
                    $sections = [];
                    foreach ($permission['sections'] as $sectionKey => $section) {
                        $sections[$sectionKey] = isset($section['roles']) ? array_keys($section['roles']) : [];
                    }
                    $data['roles'] = json_encode($sections);
                }

                $this->insert($data);
            }
        }

        // compare module has role permission and not
        $diffs = array_diff($modules, $modulePermissions);
        if ($diffs) {
            foreach ($diffs as $diffKey => $diff) {
                $data = [ 'module' => $diffKey, 'group_id' => $groupId ];
                $this->insert($data);
            }
        }

        return $this->_database->trans_complete();
    }
}