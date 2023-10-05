<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_staff_api
 *
 * @property Staff_model $staff_model
 * @property Staff_view_model $staff_view_model
 * @property Pic_level_model $pic_level_model
 * @property Company_model $company_model
 * @property Profile_model $profile_model
 * @property User_model $user_model
 * @property Closure_table $closure_table
 */
class Backend_staff_api extends Backend_Api_Controller
{
    public $_section = 'staff';

    protected $_validationRules = [
        ['field' => 'fullName', 'label' => 'lang:staff::staff:full_name', 'rules' => 'trim|required|max_length[50]'],
        ['field' => 'companyId', 'label' => 'lang:staff::staff:company', 'rules' => 'trim|required'],
        ['field' => 'phone', 'label' => 'lang:staff::staff:phone', 'rules' => 'trim|required|max_length[15]'],
        ['field' => 'nik', 'label' => 'lang:staff::staff:nik', 'rules' => 'trim|required|max_length[20]|callback__check_nik'],
        ['field' => 'position', 'label' => 'lang:staff::staff:position', 'rules' => 'trim|required|max_length[255]'],
        ['field' => 'email', 'label' => 'lang:staff::staff:email', 'rules' => 'trim|required|max_length[100]'],
        ['field' => 'picLevel', 'label' => 'lang:staff::staff:pic_level', 'rules' => 'trim|required'],
        ['field' => 'groupId', 'label' => 'User group', 'rules' => 'trim|required'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('staff/staff_model');
        $this->lang->load('staff/staff');
    }

    public function _check_nik($nik = '')
    {
        $this->load->model('users/profile_model');
        if ($this->profile_model->with_trashed()->check_unique_field('nik', $nik, $this->input->post('userId'), 'user_id')) 
        {
            $this->form_validation->set_message('_check_nik', sprintf(lang('msg::staff::staff:nik_already_exist'), $nik));
            return false;
        }

        return true;
    }

    public function _check_email($email = '')
    {
        $this->load->model('users/user_model');
        if ($this->user_model->with_trashed()->check_unique_field('email', $email, $this->input->post('userId'))) 
        {
            $this->form_validation->set_message('_check_email', sprintf(lang('msg::staff::staff:email_already_exist'), $email));
            return false;
        }

        return true;
    }

    public function index()
    {
        $results = $this->staff_model->treeGetAll();
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
        $item = $this->staff_model
            ->with('profile')
            ->with('pic_level')
            ->with('user')
            ->get(['id' => $id]);
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
            'fullName' => $item->profile->full_name,
            'nik' => $item->profile->nik,
            'phone' => $item->profile->phone,
            'position' => $item->profile->position,
//            'level' => $item->pic_level->name,
            'parentId' => $item->parent_id,
            'user' => [
                'userId' => $item->user->id,
                'username' => $item->user->username,
                'email' => $item->user->email,
                'companyId' => $item->user->company_id,
                'groupId' => $item->user->group_id,
                'active' => $item->user->active > 0,
                'lastLogin' => $item->user->last_login,
            ],
            'level' => [
                'id' => $item->pic_level_id,
                'name' => $item->pic_level->name,
                'level' => $item->pic_level->level
            ]
        ];

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
    }

    public function form_options()
    {
        // get companies
        $this->load->model('references/company_model');
        $companies = $this->company_model->fields('id, name')
            ->order_by('name')
            ->as_array()
            ->get_all(['active' => 'A']);

        // get pic level
        $this->load->model('staff/pic_level_model');
        $picLevel = $this->pic_level_model->fields('id,name,level')
            ->order_by('level')
            ->as_array()
            ->get_all();

        // get head staff
        $this->load->model('staff/staff_view_model');
        if ($this->input->get('id')) {
            $this->staff_model->where(['id !=' => $this->input->get('id')]);
        }
        $headStaff = $this->staff_view_model->fields('id,full_name AS fullName,nik,phone,position')
            ->as_array()
            ->get_all();

        // get user group can access control panel
        $this->load->model('users/group_model');
        $userGroups = $this->group_model->fields('id,name')
            ->as_array()
            ->where('view_cp', '1')
            ->where('is_admin', '1', NULL, TRUE)
            ->get_all();

        $this->template->build_json([
            'companyOptions'    => $companies ? $companies : [],
            'levelOptions'      => $picLevel ? $picLevel : [],
            'headOptions'       => $headStaff ? $headStaff : [],
            'userGroupOptions'  => $userGroups ? $userGroups : []
        ]);
    }

    public function create()
    {
        userHasRoleOrDie('create', 'staff', 'staff');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $passMinLength = $this->the_auth_backend->getMinPasswordLength();
        $passMaxLength = $this->the_auth_backend->getMaxPasswordLength();

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('password', 'lang:staff::staff:password', 'trim|required|min_length[' . $passMinLength . ']|max_length[' . $passMaxLength . ']');
        $this->form_validation->set_rules('rePassword', 'lang:staff::staff:password_confirm', 'trim|required|matches[password]');
        if ($this->form_validation->run())
        {
            $data = [
                'full_name'     => $this->input->post('fullName', TRUE),
                'nik'           => $this->input->post('nik', TRUE),
                'phone'         => $this->input->post('phone', TRUE),
                'position'      => $this->input->post('position', TRUE),
                'pic_level_id'  => $this->input->post('picLevel', TRUE),
                'email'         => $this->input->post('email', TRUE),
                'company_id'    => $this->input->post('companyId', TRUE),
                'password'      => $this->input->post('password', TRUE),
                'active'        => $this->input->post('active'),
                'group_id'      => $this->input->post('groupId'),
            ];

            $result = $this->staff_model->create($data);
            if ($result['success']) {
                Events::trigger('staff::staff:created', $result['user_id']);
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
        userHasRoleOrDie('edit', 'staff', 'staff');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        $this->form_validation->set_rules('userId', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'full_name'     => $this->input->post('fullName', TRUE),
                'nik'           => $this->input->post('nik', TRUE),
                'phone'         => $this->input->post('phone', TRUE),
                'position'      => $this->input->post('position', TRUE),
                'pic_level_id'  => $this->input->post('picLevel', TRUE),
                'email'         => $this->input->post('email', TRUE),
                'company_id'    => $this->input->post('companyId', TRUE),
                'active'        => $this->input->post('active'),
                'parent_id'     => $this->input->post('parentId') ? $this->input->post('parentId') : null,
                'group_id'      => $this->input->post('groupId'),
            ];

            $result = $this->staff_model->edit($this->input->post('id', true), $data);
            if ($result) {
                Events::trigger('staff::staff:edited', $this->input->post('id', TRUE));
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
        userHasRoleOrDie('remove', 'staff', 'staff');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $staff = $this->staff_model
            ->with('profile')
            ->get(['id' => $id]);
        if (!$staff) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $remove = $this->staff_model
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
            'message' => sprintf(lang('msg::delete_success_fmt'), $staff->profile->full_name)
        ]);

        return true;
    }

    public function testing()
    {
        $this->load->library('closure_table', [
            'table' => 'staff'
        ]);
        $node = $this->closure_table->getAllNode('26');
        print_r($node);
    }
}