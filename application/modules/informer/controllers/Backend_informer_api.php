<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_informer_api
 *
 * @property Informer_model $informer_model
 */
class Backend_informer_api extends Backend_Api_Controller
{
    public $_section = '';

    protected $_validationRules = [
        ['field' => 'fullName', 'lang:informer::full_name', 'trim|required'],
        ['field' => 'companyId', 'lang:informer::company', 'trim|required'],
        ['field' => 'companyBranchId', 'lang:informer::location', 'trim|required'],
        ['field' => 'departmentId', 'lang:informer::department', 'trim|required'],
        ['field' => 'phone', 'lang:informer::phone', 'trim|required'],
        ['field' => 'nik', 'lang:informer::nik', 'trim|required'],
        ['field' => 'position', 'lang:informer::position', 'trim|required'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('informer/informer_model');
    }

    public function index()
    {
        $this->load->library('bt_server');

        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'full_name', 'bt' => 'fullName'],
            ['db' => 'nik', 'bt' => 'nik'],
            ['db' => 'phone', 'bt' => 'phone'],
            ['db' => 'company_id', 'bt' => 'companyId'],
            ['db' => 'company_name', 'bt' => 'companyName'],
            ['db' => 'company_abbr', 'bt' => 'companyAbbr'],
            ['db' => 'department_id', 'bt' => 'departmentId'],
            ['db' => 'department_name', 'bt' => 'departmentName'],
            ['db' => 'department_other', 'bt' => 'departmentOther'],
            ['db' => 'position', 'bt' => 'position'],
            ['db' => 'username', 'bt' => 'username'],
            ['db' => 'email', 'bt' => 'email'],
            ['db' => 'last_login', 'bt' => 'lastLogin'],
            ['db' => 'active', 'bt' => 'active', 'formatter' => function ($val) {
                return $val > 0;
            }],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['full_name','nik','phone'])
            ->process($request, $columns, $this->informer_model->table . '_view');
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
        $item = $this->informer_model->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $this->load->library('references/keyword');

        $data = [
            'id' => $item->id,
            'userId' => $item->user_id,
            'fullName' => $item->full_name,
            'nik' => $item->nik,
            'phone' => $item->phone,
            'companyId' => $item->company_id,
            'companyBranchId' => $item->company_branch_id,
            'departmentId' => $item->department_id,
            'departmentOther' => $item->department_other,
            'position' => $item->position,
            'createdAt' => $item->created_at,
            'updatedAt' => $item->updated_at,
        ];

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
    }

    public function edit()
    {
        userHasRoleOrDie('edit', 'informer');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:lb::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'full_name' => $this->input->post('fullName', TRUE),
                'company_id' => $this->input->post('companyId', TRUE),
                'company_branch_id' => $this->input->post('companyBranchId', TRUE),
                'department_id' => $this->input->post('departmentId', TRUE),
                'phone' => $this->input->post('phone', TRUE),
                'nik' => $this->input->post('nik', TRUE),
                'position' => $this->input->post('position', TRUE),
            ];

            $result = $this->informer_model->update($data, ['id' => $this->input->post('id', true)]);
            if ($result) {
                Events::trigger('informer:edited', $this->input->post('id', TRUE));
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
        userHasRoleOrDie('edit', 'informer');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $department = $this->informer_model->fields('id,full_name')->get(['id' => $id]);    // find partner
        if (!$department) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->informer_model->set_before_soft_delete('add_deleted');
        $remove = $this->informer_model
            ->delete(['id' => $this->input->get('id', TRUE)]);
        if (!$remove) {
            Events::trigger('informer::removed', $this->input->get('id', true));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $department->full_name)
        ]);

        return true;
    }
}