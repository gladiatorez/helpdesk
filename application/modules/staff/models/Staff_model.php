<?php

/**
 * Class Staff_model
 *
 * @property Profile_model $profile_model
 * @property Closure_table $closure_table
 * @property Position_model $position_model
 * @property Group_model $group_model
 * @property User_model $user_model
 */
class Staff_model extends MY_Model
{
    public $table = 'staff';

    public $primary_key = 'id';

    public $fillable = array('user_id','nik','position_id','parent_id','is_ready');
    public $timestamps = FALSE;

    public $delete_cache_on_save = TRUE;

    public function __construct()
    {
        $this->has_one['profile'] = array('users/Profile_model', 'user_id', 'user_id');
        $this->has_one['user'] = array('users/User_model', 'id', 'user_id');
        $this->has_one['pic_level'] = array('staff/Pic_level_model', 'id', 'pic_level_id');
        $this->has_one['tops'] = array('staff/Staff_model', 'id', 'parent_id');
        // $this->has_many['tickets'] = array('tickets/Ticket_staff_model', 'user_id', 'user_id');

        parent::__construct();

        $this->load->library('closure_table', array('table' => $this->table));

        defined('DB_PREFIX') OR define('DB_PREFIX', $this->_database->dbprefix);
    }

    public function treeGetAll()
    {
        $this->_database->select(array(
            DB_PREFIX.'staff.id AS key', 
            DB_PREFIX.'user_profile.full_name AS title',
            DB_PREFIX.'staff.id AS id', 
            DB_PREFIX.'user_profile.full_name AS fullName',
            DB_PREFIX.'user_profile.nik AS nik',
            DB_PREFIX.'user_profile.position AS position',
            DB_PREFIX.'staff.parent_id AS parentId', 
            DB_PREFIX.'staff.parent_id AS parent',
            DB_PREFIX.'staff.pic_level_id AS picLevelId',
            DB_PREFIX.'staff_pic_level.name AS picLeveLName',
            DB_PREFIX.'staff_pic_level.level AS picLevel'
        ));
        $this->_database->where(DB_PREFIX.'staff.deleted_at IS NULL');
        $this->_database->order_by('user_profile.full_name', 'asc');
        $this->_database->join(DB_PREFIX.'user_profile', DB_PREFIX.'staff.user_id = '.DB_PREFIX.'user_profile.user_id', 'LEFT');
        $this->_database->join(DB_PREFIX.'staff_pic_level', DB_PREFIX. 'staff.pic_level_id = '.DB_PREFIX.'staff_pic_level.id', 'LEFT');
        $all = $this->_database->get($this->table)->result_array();


        $categories = array();
        foreach ($all as $row) {
            $row['expanded'] = true;
            $categories[$row['key']] = $row;
        }
        unset($all);

        // Build a multidimensional array of parent > children.
        $cat_array = array();
        foreach ($categories as $category) {
            if (array_key_exists($category['parent'], $categories)) {
                $categories[$category['parent']]['children'][] =& $categories[$category['key']];
            }
            if ($category['parent'] == 0) {
                $cat_array[] =& $categories[$category['key']];
            }
        }

        usort($cat_array, function($a, $b) {
            if (isset($a['children'])) {
                return 0;
            }
            return 1;
        });

        return $cat_array;
    }

    public function getTopsByBreadcrumb($breadcrumb = array())
    {
        if (count($breadcrumb) <= 0) {
            return false;
        }

        $result = array();
        foreach ($breadcrumb as $crumb) {
            $row = $this->_database
                ->select(array('id','user','nik','fullName','phone','photo','positionName','picLevel','parentId AS tops'))
                ->where('id', $crumb)
                ->get($this->table.'_view');

            $row->num_rows() > 0 && array_push($result, $row->row());
        }

        return $result;
    }

    public function getDetail($id)
    {
        $staff = $this->fields('id,nik,user_id AS user,position_id AS position,parent_id AS tops')
            ->with('profile', array('fields:full_name AS fullName,phone,photo_file AS photo'))
            ->with('user', array('fields:username,email,last_login AS lastLogin,active'))
            ->with('position', array('fields:id,name,pic_level AS picLevel'))
            ->get($id);

        $node = $this->closure_table->getNode($staff->id, 'ASC');
        if (!$node) {
            return false;
        }

        $nodeBreadcrumbs = explode(',', $node->breadcrumbs);
        if ($nodeBreadcrumbs > 1) {
            unset($nodeBreadcrumbs[0]);
        }
        $tops = $this->getTopsByBreadcrumb($nodeBreadcrumbs);
        if ($tops) {
            $staff->topsList = $tops;
        }

        unset($staff->profile->user_id);
        unset($staff->position_id);

        return $staff;
    }

    public function getByPosition($position)
    {
        $this->load->model('positions/position_model');
        $this->load->model('users/profile_model');
        $query = $this->_database->select(array(
            $this->table.'.user_id',
            $this->table.'.position_id',
            $this->position_model->table.'.name',
            $this->position_model->table.'.pic_level',
            $this->profile_model->table.'.full_name',
            $this->profile_model->table.'.photo_file'
        ))->join($this->profile_model->table, $this->table.'.user_id = '.$this->profile_model->table.'.user_id')
            ->join($this->position_model->table, $this->table.'.position_id = '.$this->position_model->table.'.id')
            ->where_in($this->position_model->table.'.id', $position)
            ->order_by($this->profile_model->table.'.full_name', 'asc')
            ->get($this->table);

        return $query->result();
    }

    public function getSubordinates($userId, $with = null)
    {
        $staff = $this->get(array('user_id' => $userId));
        if ($staff === FALSE) {
            return FALSE;
        }

        $query = $this->_database->query('call p_ufi_staff_get_subtree_by_node_id('.$staff->id.')');
        $result = $query->result();
        if (!$result) {
            return FALSE;
        }

        $staffIds = array();
        foreach ($result as $key => $item) {
            if ($item->id === $staff->id) {
                unset($result[$key]);
                continue;
            }
            $staffIds[] = $item->id;
        }

        if ($with) {
            $this->with($with);
        }

        if (!$staffIds) {
            return FALSE;
        }

        $subordinates = $this->where('id', 'in', $staffIds)
            ->get_all();
        if ($subordinates === FALSE) {
            return FALSE;
        }

        return $subordinates;
    }

    public function create($data)
    {
        if (!array_key_exists('full_name', $data) || !array_key_exists('nik', $data) || 
            !array_key_exists('phone', $data) || !array_key_exists('position', $data) || 
            !array_key_exists('pic_level_id', $data) || !array_key_exists('email', $data) || 
            !array_key_exists('company_id', $data) || !array_key_exists('password', $data) || 
            !array_key_exists('active', $data) || !array_key_exists('group_id', $data)) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        // get infor of user group
        $group = $this->group_model->fields('send_ticket')->get(['id' => $data['group_id']]);
        if (!$group) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        $profileData = [
            'full_name'  => $data['full_name'],
            'phone'      => $data['phone'],
            'position'   => $data['position'],
            'nik'        => $data['nik'],
        ];

        $this->_database->trans_start();

        $register = $this->the_auth_backend->register(
            '',
            $data['password'],
            $data['email'],
            $data['company_id'],
            $data['group_id'],
            $profileData,
            'en',
            $data['active']
        );
        if (!$register) {
            $this->_database->trans_rollback();
            return [
                'success' => false,
                'message' => $this->the_auth_backend->getMessageStr()
            ];
        }

        if ($group->send_ticket > 0) {
            $this->load->model('informer/informer_model');
            $informer = $this->informer_model->count_rows(['user_id' => $register]);
            if ($informer <= 0) {
                $this->informer_model->insert([
                    'user_id' => $register,
                    'full_name' => $profileData['full_name'],
                    'nik' => $profileData['nik'],
                    'position' => $profileData['position'],
                    'company_id' => $data['company_id'],
                    'phone' => $profileData['phone'],
                ]);
            }
        }

        $this->load->library('closure_table', [
            'table' => $this->table
        ]);
        $this->closure_table->add([
            'user_id' => $register,
            'parent_id' => null,
            'pic_level_id' => $data['pic_level_id']
        ]);

        $this->_database->trans_complete();
        if ($this->_database->trans_status() === false) {
            $this->_database->trans_rollback();
            return [
                'success' => false,
                'message' => lang('msg::saving_failed'),
            ];
        }

        return [
            'success' => true,
            'message' => lang('msg::saving_success'),
            'user_id' => $register
        ];
    }

    public function edit($id, $data)
    {
        if (!array_key_exists('full_name', $data) || !array_key_exists('nik', $data) ||
            !array_key_exists('phone', $data) || !array_key_exists('position', $data) ||
            !array_key_exists('pic_level_id', $data) || !array_key_exists('email', $data) ||
            !array_key_exists('company_id', $data) || !array_key_exists('active', $data) ||
            !array_key_exists('parent_id', $data) || !array_key_exists('group_id', $data)) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        // get staff
        $staff = $this->get(['id' => $id]);
        if (!$staff) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        // get infor of user group
        $group = $this->group_model->fields('send_ticket')->get(['id' => $data['group_id']]);
        if (!$group) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        $this->_database->trans_start();

        // update user
        $this->user_model->update([
            'email' => $data['email'],
            'active' => $data['active'],
            'company_id' => $data['company_id'],
            'group_id'  => $data['group_id']
        ], ['id' => $staff->user_id]);

        // update profile
        $this->profile_model->update([
            'full_name' => $data['full_name'],
            'nik' => $data['nik'],
            'phone' => $data['phone'],
            'position' => $data['position']
        ], ['user_id' => $staff->user_id]);

        // update staff
        $dataStaff = [
            'parent_id' => $data['parent_id'] ? $data['parent_id'] : null,
            'pic_level_id' => $data['pic_level_id']
        ];

        $this->closure_table->update($id, $dataStaff, false);

        if ($group->send_ticket > 0) {
            $this->load->model('informer/informer_model');
            $informer = $this->informer_model->count_rows(['user_id' => $staff->user_id]);
            if ($informer <= 0) {
                $this->informer_model->insert([
                    'user_id' => $staff->user_id,
                    'full_name' => $data['full_name'],
                    'nik' => $data['nik'],
                    'position' => $data['position'],
                    'company_id' => $data['company_id'],
                    'phone' => $data['phone'],
                ]);
            }
        }

        $this->_database->trans_complete();
        if ($this->_database->trans_status() === false) {
            $this->_database->trans_rollback();
            return [
                'success' => false,
                'message' => lang('msg::saving_failed'),
            ];
        }

        return [
            'success' => true,
            'message' => lang('msg::saving_success'),
            'user_id' => $staff->user_id
        ];
    }

}