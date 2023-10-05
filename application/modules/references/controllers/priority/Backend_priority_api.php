<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Class Backend_priority_api
 *
 * @property Priority_model $priority_model
 */
class Backend_priority_api extends Backend_Api_Controller
{
    public $_section = 'priority';

    protected $_validationRules = [
        ['field' => 'name', 'label' => 'lang:references::priority:name', 'rules' => 'trim|required|min_length[3]|callback__check_ord'],
        ['field' => 'ord', 'label' => 'lang:references::priority:order', 'rules' => 'trim|greater_than_equal_to[0]|callback__check_ord'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('references/priority_model');
        $this->lang->load('references/priority');
    }

    public function _check_name($name = '')
    {
        if ($this->priority_model->with_trashed()->check_unique_field('name', $name, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_name', sprintf(lang('msg::references::priority:name_already_exist'), $name));
            return false;
        }

        return true;
    }

    public function _check_ord($ord = '')
    {
        if (!empty($ord)) {
            if ($this->priority_model->with_trashed()->check_unique_field('ord', $ord, $this->input->post('id'))) {
                $this->form_validation->set_message('_check_ord', sprintf(lang('msg::references::priority:order_already_exist'), $name));
                return false;
            }
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
            ['db' => 'ord', 'bt' => 'ord'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['name'])
            ->process($request, $columns, $this->priority_model->table);
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

        $id = $this->input->get('id', true);
        $item = $this->priority_model->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $data = [
            'id' => $item->id,
            'name' => $item->name,
            'ord' => $item->ord,
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
        userHasRoleOrDie('create', 'references', 'priority');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run()) {
            $data = [
                'name' => $this->input->post('name', true),
                'ord' => $this->input->post('ord'),
                'created_by' => $this->the_auth_backend->getUserLoginId(),
                'updated_by' => $this->the_auth_backend->getUserLoginId()
            ];

            $result = $this->priority_model->insert($data);
            if ($result) {
                Events::trigger('references::priority:created', $result);
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
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function edit()
    {
        userHasRoleOrDie('edit', 'references', 'priority');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run()) {
            $data = [
                'name' => $this->input->post('name', true),
                'ord' => $this->input->post('ord'),
                'updated_by' => $this->the_auth_backend->getUserLoginId()
            ];

            $result = $this->priority_model->update($data, ['id' => $this->input->post('id', true)]);
            if ($result) {
                Events::trigger('references::priority:edited', $this->input->post('id', true));
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
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function remove()
    {
        userHasRoleOrDie('remove', 'references', 'priority');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', true);
        $priority = $this->priority_model->fields('id,name')->get(['id' => $id]);    // find partner
        if (!$priority) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->priority_model->set_before_soft_delete('add_deleted');
        $remove = $this->priority_model
            ->delete(['id' => $this->input->get('id', true)]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $priority->name)
        ]);

        return true;
    }
}