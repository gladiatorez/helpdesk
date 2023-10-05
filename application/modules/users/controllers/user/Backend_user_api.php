<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_user_api
 *
 * @property User_model $user_model
 * @property Group_model $group_model
 * @property Profile_model $profile_model
 * @property Company_model $company_model
 * @property The_auth_backend $the_auth_backend
 */
class Backend_user_api extends Backend_Api_Controller
{
    public $_section = 'user';

    protected $_validationRules = [
        ['field' => 'username', 'label' => 'lang:users::user:username', 'rules' => 'trim|required|max_length[50]'],
        ['field' => 'email', 'label' => 'lang:users::user:email', 'rules' => 'trim|required|max_length[100]|callback__check_email'],
        // ['field' => 'group_id', 'label' => 'lang:users::user:group', 'rules' => 'trim|required'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('users/user_model');
        $this->lang->load('users/user');
    }

    public function index()
    {
        $this->load->library('bt_server');

        $this->load->model('users/profile_model');
        $this->load->model('users/group_model');
        $this->load->model('references/company_model');

        $request = $this->input->get();
        $userTable = $this->user_model->table;
        $columns = [
            ['db' => $userTable.'.id', 'bt' => 'id'],
            ['db' => $userTable.'.username', 'bt' => 'username'],
            ['db' => $userTable.'.email', 'bt' => 'email'],
            ['db' => $userTable.'.active', 'bt' => 'active', 'formatter' => function($val) {
                return $val >= 1;
            }],
            ['db' => $userTable.'.lang', 'bt' => 'lang'],
            ['db' => $userTable.'.group_id', 'bt' => 'groupId'],
            ['db' => $userTable.'.company_id', 'bt' => 'companyId'],
            ['db' => $userTable.'.last_login', 'bt' => 'lastLogin'],
            ['db' => $userTable.'.created_at', 'bt' => 'createdAt'],
            ['db' => $userTable.'.updated_at', 'bt' => 'updatedAt'],
        ];
        $groupTable = $this->group_model->table;
        $companyTable = $this->company_model->table;

        $results = $this->bt_server
            ->setTableJoin(
                $groupTable,
                sprintf('%s.group_id = %s.id' , $userTable, $groupTable),
                [ ['db' => $groupTable.'.name', 'bt' => 'groupName'] ],
                'LEFT'
            )
            ->setTableJoin(
                $companyTable,
                sprintf('%s.company_id = %s.id' , $userTable, $companyTable),
                [ 
                    ['db' => $companyTable.'.abbr', 'bt' => 'companyAbbr'],
                    ['db' => $companyTable . '.name', 'bt' => 'companyName']
                ],
                'LEFT'
            )
            ->setSearchColumns(['username','email'])
            ->process($request, $columns, $userTable);
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
        $item = $this->user_model->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $item->active = (bool) $item->active;

        unset($item->password);
        unset($item->salt);

        $this->template->build_json([
            'success' => true,
            'data' => $item
        ]);
        return true;
    }

    public function form_options()
    {
        // get user groups
        $this->load->model('users/group_model');
        $groups = $this->group_model->as_array()
            ->fields('id,name')
            ->order_by('name')
            ->get_all();
        
        // get company
        $this->load->model('references/company_model');
        $companies = $this->company_model->as_array()
            ->fields('id,abbr')
            ->order_by('abbr')
            ->get_all(['active' => 'A']);
        
        $this->template->build_json([
            'groups' => $groups ? $groups : [],
            'companies' => $companies ? $companies : [],
        ]);
    }

    // public function create()
    // {
    //     userHasRoleOrDie('create', 'users', 'user');

    //     $this->load->library('form_validation');
    //     $this->form_validation->CI =& $this;

    //     $passMinLength = $this->the_auth->getMinPasswordLength();
    //     $passMaxLength = $this->the_auth->getMaxPasswordLength();

    //     $this->form_validation->set_rules($this->_validationRules);
    //     $this->form_validation->set_rules('password', 'lang:users::user:password', 'trim|required|min_length['.$passMinLength.']|max_length['.$passMaxLength.']');
    //     $this->form_validation->set_rules('rePassword', 'lang:users::user:rePassword', 'trim|required|matches[password]');
    //     if ($this->the_auth->getLoginIdentity() === 'username') {
    //         $this->form_validation->set_rules('username', 'lang:users::user:username', 'trim|required|min_length[5]|max_length[100]|callback__check_username');
    //     }

    //     if ($this->form_validation->run())
    //     {
    //         $profile = [
    //             'full_name'  => $this->input->post('fullName', TRUE),
    //             'phone'      => $this->input->post('phone', TRUE),
    //             'position'   => $this->input->post('position', TRUE),
    //             'nik'        => $this->input->post('nik', TRUE),
    //         ];
    //         $register = $this->the_auth->register(
    //             $this->input->post('username'),
    //             $this->input->post('password'),
    //             $this->input->post('email'),
    //             $this->input->post('branchId'),
    //             $this->input->post('groupId'),
    //             $profile,
    //             'id',
    //             $this->input->post('active')
    //         );
    //         if ($register) {
    //             Events::trigger('users::user:created', $register);
    //             $this->template->build_json([
    //                 'success' => true,
    //                 'message' => $this->the_auth->getMessageStr()
    //             ]);
    //         } else {
    //             $this->template->build_json([
    //                 'success' => false,
    //                 'message' => $this->the_auth->getErrorStr()
    //             ]);
    //         }
    //     }
    //     else {
    //         $this->output->set_status_header('400', lang('msg::saving_failed'));
    //         $this->template->build_json([
    //             'success' => false,
    //             'message' => $this->form_validation->error_array()
    //         ]);
    //     }
    // }

    /**
     * todo edit user list
     */
    public function edit()
    {
        userHasRoleOrDie('edit', 'users', 'user');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);

        if ($this->form_validation->run())
        {
            $id = $this->input->post('id', TRUE);
            $userData = [
                'username'  => $this->input->post('username', TRUE),
                'email'     => $this->input->post('email', TRUE),
                // 'group_id'  => $this->input->post('group_id'),
                'active'    => (bool) $this->input->post('active'),
            ];

            $this->user_model->update($userData, ['id' => $id]);
            
            Events::trigger('users::user:edited', $this->input->post('id', TRUE));
            $this->template->build_json([
                'success' => true,
                'message' => lang('msg::saving_success')
            ]);
        }
        else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function _check_email($email = '')
    {
        if ($this->user_model->with_trashed()->check_unique_field('email', $email, $this->input->post('id')))
        {
            $this->form_validation->set_message('_check_email', sprintf(lang('msg::users::user:email_already_exist'), $email));
            return false;
        }

        return true;
    }

    public function _check_username($username = '')
    {
        if ($this->user_model->with_trashed()->check_unique_field('username', $username, $this->input->post('id')))
        {
            $this->form_validation->set_message('_check_username', sprintf(lang('msg::users::user:username_already_exist'), $username));
            return false;
        }

        return true;
    }

    public function remove()
    {
        userHasRoleOrDie('remove', 'users', 'user');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $profile = $this->profile_model->fields('user_id,full_name')->get(['user_id' => $id]);
        if (!$profile) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $remove = $this->the_auth->deleteUser($id);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => $this->the_auth->getErrorStr()
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $profile->full_name)
        ]);

        return true;
    }

    public function get_groups()
    {
        $this->load->model('users/group_model');
        $groups = $this->group_model
            ->fields('id,name')
            ->order_by('name', 'ASC')
            ->as_array()
            ->get_all();
        if (!$groups) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::users::user:group_empty')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $groups
        ]);
        return true;
    }

    public function get_branch()
    {
        $this->load->model('reference/branch_office_model');
        $offices = $this->branch_office_model
            ->fields('id,name')
            ->order_by('name', 'ASC')
            ->as_array()
            ->get_all(['active' => 'A']);
        if (!$offices) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::users::user:branch_office_empty')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $offices
        ]);
        return true;
    }

    public function change_password()
    {
        userHasRoleOrDie('change_password', 'users', 'user');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $passMinLength = $this->the_auth->getMinPasswordLength();
        $passMaxLength = $this->the_auth->getMaxPasswordLength();

        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        $this->form_validation->set_rules('newPassword', 'lang:users::user:password', 'trim|required|min_length['.$passMinLength.']|max_length['.$passMaxLength.']');
        $this->form_validation->set_rules('reNewPassword', 'lang:users::user:rePassword', 'trim|required|matches[newPassword]');
        if ($this->form_validation->run())
        {
            $changed = $this->the_auth->changeUserPassword(
                $this->input->post('id', TRUE),
                $this->input->post('newPassword', TRUE)
            );
            if ($changed) {
                Events::trigger('users::user:changed_password', $this->input->post('id', TRUE));
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_auth->getMessageStr()
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_auth->getErrorStr()
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
}