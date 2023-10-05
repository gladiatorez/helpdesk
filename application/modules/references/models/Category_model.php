<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * class Category_model
 * 
 * @property Category_staff_model $category_staff_model
 * @property Staff_model $staff_model
 */
class Category_model extends MY_Model
{
    public $table = 'ref_categories';
    public $primary_key = 'id';
    public $fillable = [
        'parent_id', 'name', 'description', 'icon_class', 'estimate', 'active',
        'auto_priority', 'auto_send_staff',
        'created_by', 'updated_by', 'deleted_by', 'task'
    ];

    public function __construct()
    {
        $this->soft_deletes = TRUE;
        $this->has_many['staff'] = [
            'references/Category_staff_model', 'category_id', 'id'
        ];

        $this->has_one['top'] = [
            'references/Category_model', 'id', 'parent_id'
        ];

        $this->has_many['child'] = [
            'references/Category_model', 'parent_id', 'id'
        ];

        parent::__construct();
    }

    public function treeGetAll()
    {
        $this->_database->select('id as key, name as title, name as name, id as id, parent_id as parent,
                            parent_id as parentId, active as status, task as task, estimate as estimate, description as description,
                            DATE_FORMAT(created_at, "%d/%m/%Y") as createdAt, 
                            DATE_FORMAT(updated_at, "%d/%m/%Y") as updatedAt');
        $this->_database->where('deleted_at IS NULL');
        $this->_database->order_by('name', 'asc');
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
                $categories[$category['parent']]['children'][] = &$categories[$category['key']];
            }
            if ($category['parent'] == 0) {
                $cat_array[] = &$categories[$category['key']];
            }
        }

        return $cat_array;
    }

    public function create($data)
    {
        if (!array_key_exists('name', $data)) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('name', $data['name'])) {
            return false;
        }

        $this->set_before_create('add_creator');
        $this->set_before_create('add_updater');

        $this->load->model('references/category_staff_model');
        $this->load->model('staff/staff_model');

        $this->_database->trans_start();

        // insert category
        $insert = $this->insert([
            'parent_id'         => $data['parent_id'],
            'name'              => $data['name'],
            'description'       => $data['description'],
            'icon_class'        => $data['icon_class'],
            'estimate'          => $data['estimate'],
            'auto_priority'     => $data['auto_priority'],
            'auto_send_staff'   => $data['auto_send_staff'],
            'active'            => $data['active'],
            'task'               => $data['task']
        ]);

        
        // insert staffs
        $staffs = $data['staff'];
        unset($data['staff']);
        if (is_array($staffs) && count($staffs) > 0) {
            $allStaff = $this->staff_model->where('id', $staffs)
                ->get_all();
            $this->category_staff_model->set_before_create('add_creator');
            $this->category_staff_model->set_before_update('add_updater');
            foreach ($allStaff as $staff) {
                $this->category_staff_model->insert([
                    'category_id' => $insert,
                    'user_id' => $staff->user_id
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
            'id' => $insert
        ];
    }

    public function edit($id, $data)
    {
        if (!array_key_exists('name', $data)) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed'),
            ];
        }

        if ($this->with_trashed()->check_unique_field('name', $data['name'], $id)) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed'),
            ];
        }

        $this->load->model('references/category_staff_model');
        $this->load->model('staff/staff_model');

        $this->_database->trans_start();

        // update category
        $this->set_before_update('add_updater');
        $this->update([
            'parent_id'         => $data['parent_id'],
            'name'              => $data['name'],
            'description'       => $data['description'],
            // 'icon_class'        => $data['icon_class'],
            'estimate'          => $data['estimate'],
            'auto_priority'     => $data['auto_priority'],
            'auto_send_staff'   => $data['auto_send_staff'],
            'active'            => $data['active'],
            'task'            => $data['task']
        ], ['id' => $id]);

        
        // update staffs
        $this->category_staff_model->delete(['category_id' => $id]);
        $staffs = $data['staff'];
        unset($data['staff']);
        if (is_array($staffs) && count($staffs) > 0) {
            
            $allStaff = $this->staff_model->where('id', $staffs)
                ->get_all();
                
    
            $this->category_staff_model->set_before_create('add_creator');
            $this->category_staff_model->set_before_update('add_updater');
            foreach ($allStaff as $staff) {
               $tes= $this->category_staff_model->insert([
                    'category_id'   => $id,
                    'user_id'       => $staff->user_id
                ]);

                
            }
        }

        $this->_database->trans_complete();
        if ($this->_database->trans_status() === FALSE) {
            $this->_database->trans_rollback();
            return [
                'success' => false,
                'message' => lang('msg::saving_failed'),
            ];
        }

        return [
            'success' => true,
            'message' => lang('msg::saving_success'),
            'id' => $id
        ];
    }
}