<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_company_api
 *
 * @property Company_model $company_model
 * @property Company_branch_model $company_branch_model
 * @property Bt_server $bt_server
 */
class Backend_company_api extends Backend_Api_Controller
{
    public $_section = 'company';

    protected $_validationRules = [
        ['field' => 'name', 'label' => 'lang:references::company:name', 'rules' => 'trim|required|max_length[255]|callback__check_name'],
        ['field' => 'abbr', 'label' => 'lang:references::company:abbr', 'rules' => 'trim|required|min_length[2]|max_length[10]|callback__check_abbr'],
        ['field' => 'email', 'label' => 'lang:references::company:email', 'rules' => 'trim'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('references/company_model');
        $this->lang->load('references/company');
    }

    public function _check_name($name = '')
    {
        if ($this->company_model->with_trashed()->check_unique_field('name', $name, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_name', sprintf(lang('msg::references::company:name_already_exist'), $name));
            return false;
        }

        return true;
    }

    public function _check_abbr($name = '')
    {
        if ($this->company_model->with_trashed()->check_unique_field('abbr', $name, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_abbr', sprintf(lang('msg::references::company:abbr_already_exist'), $name));
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
            ['db' => 'abbr', 'bt' => 'abbr'],
            ['db' => 'address', 'bt' => 'address'],
            ['db' => 'telephone', 'bt' => 'telephone'],
            ['db' => 'faximile', 'bt' => 'faximile'],
            ['db' => 'email', 'bt' => 'email'],
            ['db' => 'website', 'bt' => 'website'],
            ['db' => 'active', 'bt' => 'active'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['name','abbr'])
            ->process($request, $columns, $this->company_model->table);
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
        $item = $this->company_model->get(['id' => $id]);
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
            'abbr'      => $item->abbr,
            'address'   => $item->address,
            'telephone' => $item->telephone,
            'faximile'  => $item->faximile,
            'email'     => $item->email,
            'website'   => $item->website,
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
        userHasRoleOrDie('create', 'references', 'company');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run())
        {
            $data = [
                'name'      => $this->input->post('name', TRUE),
                'abbr'      => $this->input->post('abbr', TRUE),
                'address'   => $this->input->post('address', TRUE),
                'telephone' => $this->input->post('telephone', TRUE),
                'faximile'  => $this->input->post('faximile', TRUE),
                'email'     => $this->input->post('email', TRUE),
                'website'   => $this->input->post('website', TRUE),
                'active'    => $this->input->post('active'),
            ];

            $result = $this->company_model->create($data);
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
        userHasRoleOrDie('edit', 'references', 'company');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'name'      => $this->input->post('name', TRUE),
                'abbr'      => $this->input->post('abbr', TRUE),
                'address'   => $this->input->post('address', TRUE),
                'telephone' => $this->input->post('telephone', TRUE),
                'faximile'  => $this->input->post('faximile', TRUE),
                'email'     => $this->input->post('email', TRUE),
                'website'   => $this->input->post('website', TRUE),
                'active'    => $this->input->post('active'),
            ];

            $result = $this->company_model->edit($this->input->post('id', true), $data);
            if ($result) {
                Events::trigger('references::company:edited', $this->input->post('id', TRUE));
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
        userHasRoleOrDie('remove', 'references', 'company');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $company = $this->company_model->fields('id,name')->get(['id' => $id]);    // find partner
        if (!$company) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->company_model->set_before_soft_delete('add_deleted');
        $remove = $this->company_model
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
            'message' => sprintf(lang('msg::delete_success_fmt'), $company->name)
        ]);

        return true;
    }

    public function branchlist()
    {
        if ($this->input->get('companyId')) 
        {
            $this->load->library('bt_server');
            $this->load->model('references/company_branch_model');
            $request = $this->input->get();
            $columns = [
                ['db' => 'id', 'bt' => 'id'],
                ['db' => 'company_id', 'bt' => 'companyId'],
                ['db' => 'name', 'bt' => 'name'],
                ['db' => 'active', 'bt' => 'active'],
                ['db' => 'created_at', 'bt' => 'createdAt'],
                ['db' => 'updated_at', 'bt' => 'updatedAt'],
            ];

            $companyId = $this->input->get('companyId');
            $request = $this->bt_server->addFilterRequest('companyId', $companyId, $request);

            $results = $this->bt_server->process($request, $columns, $this->company_branch_model->table);
            $this->template->build_json($results);
        }
    }

    public function branchnew()
    {
        userHasRoleOrDie('create', 'references', 'company');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('name', 'Branch name', 'trim|required');
        $this->form_validation->set_rules('companyId', 'Company', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'name'          => $this->input->post('name', TRUE),
                'company_id'    => $this->input->post('companyId', TRUE),
                'active'        => $this->input->post('active'),
            ];

            $this->load->model('references/company_branch_model');
            $result = $this->company_branch_model->create($data);
            if ($result) {
                Events::trigger('references::company:branch_created', $result);
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

    public function branchedit()
    {
        userHasRoleOrDie('edit', 'references', 'company');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('id', 'id', 'trim|required');
        $this->form_validation->set_rules('name', 'Branch name', 'trim|required');
        $this->form_validation->set_rules('companyId', 'Company', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'name'      => $this->input->post('name', TRUE),
                'active'    => $this->input->post('active'),
                'company_id' => $this->input->post('companyId', true),
            ];

            $this->load->model('references/company_branch_model');
            $result = $this->company_branch_model->edit($this->input->post('id', true), $data);
            if ($result) {
                Events::trigger('references::company:branch_edited', $this->input->post('id', TRUE));
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

    public function branchremove()
    {
        userHasRoleOrDie('remove', 'references', 'company');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', true);
        $this->load->model('references/company_branch_model');
        $company = $this->company_branch_model->fields('id,name')->get(['id' => $id]);    // find partner
        if (!$company) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->company_branch_model->set_before_soft_delete('add_deleted');
        $remove = $this->company_branch_model
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
            'message' => sprintf(lang('msg::delete_success_fmt'), $company->name)
        ]);

        return true;
    }
}