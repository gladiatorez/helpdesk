<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_email_list_api
 *
 * @property Email_list_model $email_list_model
 */
class Backend_email_list_api extends Backend_Api_Controller
{
    public $_section = 'email_list';

    protected $_validationRules = [
        ['field' => 'email', 'label' => 'lang:references::email_list:email', 'rules' => 'trim|required|max_length[100]|valid_email|callback__check_name'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('references/email_list_model');
        $this->lang->load('references/email_list');
    }

    public function _check_name($name = '')
    {
        if ($this->email_list_model->with_trashed()->check_unique_field('email', $name, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_name', sprintf(lang('msg::references::email_list:email_already_exist'), $name));
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
            ['db' => 'email', 'bt' => 'email'],
            ['db' => 'active', 'bt' => 'active'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['email'])
            ->process($request, $columns, $this->email_list_model->table);
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
        $item = $this->email_list_model->get(['id' => $id]);
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
            'email'     => $item->email,
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
        userHasRoleOrDie('create', 'references', 'email_list');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run())
        {
            $data = [
                'email'         => $this->input->post('email', TRUE),
                'active'        => $this->input->post('active'),
                'created_by'    => $this->the_auth_backend->getUserLoginId(),
                'updated_by' => $this->the_auth_backend->getUserLoginId()
            ];

            $result = $this->email_list_model->insert($data);
            if ($result) {
                Events::trigger('references::email_list:created', $result);
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
        userHasRoleOrDie('edit', 'references', 'email_list');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'email'         => $this->input->post('email', TRUE),
                'active'        => $this->input->post('active'),
                'updated_by'    => $this->the_auth_backend->getUserLoginId()
            ];

            $result = $this->email_list_model->update($data, ['id' => $this->input->post('id', true)]);
            if ($result) {
                Events::trigger('references::email_list:edited', $this->input->post('id', TRUE));
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
        userHasRoleOrDie('remove', 'references', 'email_list');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $email_list = $this->email_list_model->fields('id,email')->get(['id' => $id]);  
        if (!$email_list) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->email_list_model->set_before_soft_delete('add_deleted');
        $remove = $this->email_list_model
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
            'message' => sprintf(lang('msg::delete_success_fmt'), $email_list->email)
        ]);

        return true;
    }
}