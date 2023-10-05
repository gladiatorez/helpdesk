<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_keyword_api
 *
 * @property Keyword_model $keyword_model
 */
class Backend_keyword_api extends Backend_Api_Controller
{
    public $_section = 'keyword';

    protected $_validationRules = [
        ['field' => 'name', 'label' => 'lang:references::keyword:name', 'rules' => 'trim|required|min_length[50]'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('references/keyword_model');
        $this->lang->load('references/keyword');
    }

    public function index()
    {
        $this->load->library('bt_server');
        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'name', 'bt' => 'name'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['name'])
            ->process($request, $columns, $this->keyword_model->table);
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
        $item = $this->keyword_model->get(['id' => $id]);
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
            'descr'     => $item->description,
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
        userHasRoleOrDie('create', 'references', 'keyword');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run())
        {
            $data = [
                'description'   => $this->input->post('descr', TRUE),
                'active'        => $this->input->post('active'),
                'created_by'    => $this->the_auth_backend->getUserLoginId(),
                'updated_by' => $this->the_auth_backend->getUserLoginId()
            ];

            $result = $this->keyword_model->insert($data);
            if ($result) {
                Events::trigger('references::keyword:created', $result);
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
        userHasRoleOrDie('edit', 'references', 'keyword');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'description'   => $this->input->post('descr', TRUE),
                'active'        => $this->input->post('active'),
                'updated_by'    => $this->the_auth_backend->getUserLoginId()
            ];

            $result = $this->keyword_model->update($data, ['id' => $this->input->post('id', true)]);
            if ($result) {
                Events::trigger('references::keyword:edited', $this->input->post('id', TRUE));
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
        userHasRoleOrDie('remove', 'references', 'keyword');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $keyword = $this->keyword_model->fields('id,description')->get(['id' => $id]);    // find partner
        if (!$keyword) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->keyword_model->set_before_soft_delete('add_deleted');
        $remove = $this->keyword_model
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
            'message' => sprintf(lang('msg::delete_success_fmt'), $keyword->description)
        ]);

        return true;
    }
}