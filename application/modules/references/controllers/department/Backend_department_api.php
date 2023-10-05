<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_department_api
 *
 * @property Department_model $department_model
 */
class Backend_department_api extends Backend_Api_Controller
{
    public $_section = 'department';

    protected $_validationRules = [
        ['field' => 'name', 'label' => 'lang:references::department:name', 'rules' => 'trim|required|max_length[255]|callback__check_name'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('references/department_model');
        $this->lang->load('references/department');
    }

    public function _check_name($name = '')
    {
        if ($this->department_model->with_trashed()->check_unique_field('name', $name, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_name', sprintf(lang('msg::references::department:name_already_exist'), $name));
            return false;
        }

        return true;
    }

    public function index()
    {
        $this->load->library('bt_server');
        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'name', 'bt' => 'name'],
            ['db' => 'active', 'bt' => 'active'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['name'])
            ->process($request, $columns, $this->department_model->table);
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
        $item = $this->department_model->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $data = [
            'id'        => $item->id,
            'name'      => $item->name,
            'active'    => $item->active,
            'createdAt' => $item->created_at,
            'updatedAt' => $item->updated_at,
        ];

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
    }

    public function create()
    {
        userHasRoleOrDie('create', 'references', 'department');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run())
        {
            $data = [
                'name'      => $this->input->post('name', TRUE),
                'active'    => $this->input->post('active'),
            ];

            $result = $this->department_model->create($data);
            if ($result) {
                Events::trigger('references::department:created', $result);
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
        userHasRoleOrDie('edit', 'references', 'department');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'name'      => $this->input->post('name', TRUE),
                'active'    => $this->input->post('active'),
            ];

            $result = $this->department_model->edit($this->input->post('id', true), $data);
            if ($result) {
                Events::trigger('references::department:edited', $this->input->post('id', TRUE));
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
        userHasRoleOrDie('remove', 'references', 'department');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $department = $this->department_model->fields('id,name')->get(['id' => $id]);    // find partner
        if (!$department) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->department_model->set_before_soft_delete('add_deleted');
        $remove = $this->department_model
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
            'message' => sprintf(lang('msg::delete_success_fmt'), $department->name)
        ]);

        return true;
    }
}