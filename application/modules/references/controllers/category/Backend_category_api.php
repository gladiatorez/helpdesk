<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_category_api
 *
 * @property Category_model $category_model
 * @property Staff_view_model $staff_view_model
 */
class Backend_category_api extends Backend_Api_Controller
{
    public $_section = 'category';

    protected $_validationRules = [
        ['field' => 'name', 'label' => 'lang:references::category:name', 'rules' => 'trim|required|max_length[100]|callback__check_name'],
        ['field' => 'parentId', 'label' => 'lang:references::category:parent', 'rules' => 'trim|callback__check_parent'],
        ['field' => 'descr', 'label' => 'lang:references::category:descr', 'rules' => 'trim'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('references/category_model');
        $this->lang->load('references/category');
    }

    public function _check_name($name = '')
    {
        if ($this->category_model->with_trashed()->check_unique_field('name', $name, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_name', sprintf(lang('msg::references::category:name_already_exist'), $name));
            return false;
        }

        return true;
    }

    public function _check_parent($parentId = '')
    {
        if (!empty($parentId)) {
            if ($this->category_model->count_rows(['id' => $parentId]) <= 0) {
                $this->form_validation->set_message('_check_parent', lang('msg::references::category:parent_not_found'));
                return false;
            }
        }

        return true;
    }

    public function index()
    {
        $results = $this->category_model->treeGetAll();
        $this->template->build_json($results);
    }

    public function item()
    {
        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $item = $this->category_model->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        // get staff of category
        $this->load->model('references/category_staff_model');
        $categoryStaff = $this->category_staff_model
            ->with('staff')
            ->get_all(['category_id' => $item->id]);
        $staffs = [];
        if ($categoryStaff) {
            foreach ($categoryStaff as $row) {
                array_push($staffs, $row->staff->id);
            }
        }

        $data = [
            'id'            => $item->id,
            'parentId'      => $item->parent_id > 0 ? $item->parent_id : null,
            'name'          => $item->name,
            'descr'         => $item->description,
            'icon'          => $item->icon_class,
            'estimate'      => (int)$item->estimate,
            'active'        => $item->active,
            'staff'         => $staffs,
            'priorityId'    => $item->auto_priority,
            'autoSendStaff' => (bool) $item->auto_send_staff,
            'createdAt'     => $item->created_at,
            'updatedAt'     => $item->updated_at,
            'task'      => $item->task,
        ];

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
    }

    public function sub_category()
    {
        
        $parentCategory = $this->input->get('parent_id');
        
        // get sub parent category
        $parents = $this->category_model->fields('id, name')
            ->order_by('name')
            ->as_array()
            ->get_all(['parent_id' => $parentCategory, 'estimate' => '0', 'task' => '0']);

     
        $this->template->build_json([
            'parentOptions' => $parents ? $parents : []
        ]);
    }

    public function form_options()
    {
        // get parent category
        $parents_parents = $this->category_model->fields('id, name')
            ->order_by('name')
            ->as_array()
            ->get_all(['parent_id =' => 0]);
        
        // get sub parent category
        $parents = $this->category_model->fields('id, name')
            ->order_by('name')
            ->as_array()
            ->get_all(['parent_id >' => 0, 'estimate' => '0', 'task' => '0']);

        // get staff
        $this->load->model('staff/staff_view_model');
        $staff = $this->staff_view_model->fields('id,full_name AS fullName,position')
            ->as_array()
            ->order_by('full_name')
            ->get_all(['active' => '1']);
        
        // get priority
        $this->load->model('references/priority_model');
        $priorities = $this->priority_model->fields('id,name')
            ->as_array()
            ->order_by('ord')
            ->get_all();
        
        $this->template->build_json([
            'parent_parentOptions' => $parents_parents ? $parents_parents : [],
            'parentOptions' => $parents ? $parents : [],
            'staffOptions' => $staff ? $staff : [],
            'priorityOptions' => $priorities ? $priorities : []
        ]);
    }

    public function create()
    {
        userHasRoleOrDie('create', 'references', 'category');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->input->post('parentId') > 0) {
            $this->form_validation->set_rules('estimate', 'lang:references::category:estimate', 'trim|required|integer|greater_than_equal_to[0]');

            if ($this->input->post('autoSendStaff') > 0) {
                $this->form_validation->set_rules('staff[]', 'lang:references::category:staff', 'trim|required');
            }
        }
        if ($this->form_validation->run())
        {
            if ($this->input->post('parent_parentId') > 0 and $this->input->post('parentId') == 0) {
             
            $data = [
                'parent_id'         => $this->input->post('parent_parentId') > 0 ? $this->input->post('parent_parentId') : '0',
                'name'              => $this->input->post('name'),
                'description'       => $this->input->post('descr'),
                'icon_class'        => '',
                'estimate'          => '0',
                'auto_priority'     => null,
                'auto_send_staff'   => '0',
                'active'            => $this->input->post('active'),
                'staff'             => [],
                'task'             => $this->input->post('task')
            ];
            } else {
                $data = [
                'parent_id'         => $this->input->post('parentId') > 0 ? $this->input->post('parentId') : '0',
                'name'              => $this->input->post('name'),
                'description'       => $this->input->post('descr'),
                'icon_class'        => '',
                'estimate'          => '0',
                'auto_priority'     => null,
                'auto_send_staff'   => '0',
                'active'            => $this->input->post('active'),
                'staff'             => [],
                'task'             => $this->input->post('task')
                ];
            }

            if ($this->input->post('parentId')) {
                $data['estimate']           = $this->input->post('estimate');
                $data['auto_priority']      = $this->input->post('priorityId') ? $this->input->post('priorityId') : null;
                $data['auto_send_staff']    = $this->input->post('autoSendStaff');
                $data['staff']              = $this->input->post('staff') ? $this->input->post('staff') : [];
            }

            $result = $this->category_model->create($data);
            if ($result) {
                Events::trigger('references::company:created', $result);
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        }
        else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function edit()
    {
        userHasRoleOrDie('edit', 'references', 'category');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run())
        {
            if ($this->input->post('parent_parentId') > 0 and $this->input->post('parentId') == 0) {
            $data = [
                'parent_id'         => $this->input->post('parent_parentId') > 0 ? $this->input->post('parent_parentId') : '0',
                'name'              => $this->input->post('name'),
                'description'       => $this->input->post('descr'),
                'icon_class'        => '',
                'estimate'          => '0',
                'auto_priority'     => null,
                'auto_send_staff'   => '0',
                'active'            => $this->input->post('active'),
                'staff'             => [],
                'task'             => $this->input->post('task')
            ];
            } else {
                $data = [
                'parent_id'         => $this->input->post('parentId') > 0 ? $this->input->post('parentId') : '0',
                'name'              => $this->input->post('name'),
                'description'       => $this->input->post('descr'),
                'icon_class'        => '',
                'estimate'          => '0',
                'auto_priority'     => null,
                'auto_send_staff'   => '0',
                'active'            => $this->input->post('active'),
                'staff'             => [],
                'task'             => $this->input->post('task')
                ];
            }

            if ($this->input->post('parentId')) {
                $data['estimate']           = $this->input->post('estimate');
                $data['auto_priority']      = $this->input->post('priorityId') ? $this->input->post('priorityId') : null;
                $data['auto_send_staff']    = $this->input->post('autoSendStaff');
                $data['staff']              = $this->input->post('staff') ? $this->input->post('staff') : [];
            }

            $result = $this->category_model->edit($this->input->post('id', true), $data);
            if ($result) {
                Events::trigger('references::category:edited', $this->input->post('id', TRUE));
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        }
        else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function remove()
    {
        userHasRoleOrDie('remove', 'references', 'category');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $category = $this->category_model->fields('id,name')->get(['id' => $id]); 
        if (!$category) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        // find sub category
        $subCategory = $this->category_model->count_rows(['parent_id' => $category->id]);
        if ($subCategory > 0) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::references::category:cant_delete_parent_has_child')
            ]);

            return false;
        }

        $this->category_model->set_before_soft_delete('add_deleted');
        $remove = $this->category_model
            ->delete(['id' => $this->input->get('id', TRUE)]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $category->name)
        ]);

        return true;
    }
}