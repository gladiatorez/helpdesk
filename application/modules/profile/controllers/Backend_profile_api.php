<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_profile_api
 *
 * @property User_model $user_model
 * @property Profile_model $profile_model
 */
class Backend_profile_api extends Backend_Api_Controller
{
    protected $_validationRules = [
        ['field' => 'fullName', 'label' => 'lang:profile::full_name', 'rules' => 'trim|required|max_length[50]'],
        ['field' => 'nik', 'label' => 'lang:profile::nik', 'rules' => 'trim|required|max_length[15]|callback__check_nik'],
        ['field' => 'position', 'label' => 'lang:profile::position', 'rules' => 'trim|required|max_length[255]|min_length[5]'],
        ['field' => 'email', 'label' => 'lang:profile::email', 'rules' => 'trim|required|max_length[100]|min_length[5]|callback__check_email'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('profile/profile');
        $this->load->model('profile/profile_model');
    }

    public function index()
    {
        if (empty($this->_currentUser)) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $accountProfile = $this->the_auth_backend->getUserLogin();
        if (!$accountProfile) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $this->load->model('references/company_model');
        $company = $this->company_model->set_cache('company_' . $accountProfile->company_id)
            ->get(['id' => $accountProfile->company_id]);

        $row = new stdClass();
        $row->fullName = $accountProfile->profile->full_name;
        $row->phone = $accountProfile->profile->phone;
        $row->nik = $accountProfile->profile->nik;
        $row->position = $accountProfile->profile->position;
        $row->companyId = $accountProfile->company_id;
        $row->companyName = $company ? $company->name : '';
        $row->email = $accountProfile->email;
        $row->username = $accountProfile->username;
        $row->telegramUser = $accountProfile->telegram_user;
        $row->lastLogin = $accountProfile->last_login;

        $this->template->build_json([
            'success' => true,
            'row' => $row
        ]);

        return true;
    }

    public function savegeneral()
    {
        $userId = $this->the_auth_backend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::saving_failed')
            ]);
            return false;
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        $this->form_validation->set_rules('fullName', 'lang:profile::full_name', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('phone', 'lang:profile::phone', 'trim|required|max_length[15]');
        if ($this->form_validation->run()) {
            $data = [
                'full_name' => $this->input->post('fullName', true),
                'phone'     => $this->input->post('phone'),
            ];

            $result = $this->profile_model->update($data, ['user_id' => $userId]);
            if ($result) {
                $this->load->model('users/user_model');
                $this->user_model->delete_cache('*');
                Events::trigger('backend::profile:generalinfo_updated', $userId);
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

    public function savecompany()
    {
        $userId = $this->the_auth_backend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::saving_failed')
            ]);
            return false;
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('companyId', 'lang:profile::company', 'trim|required');
        $this->form_validation->set_rules('nik', 'lang:profile::nik', 'trim|required|max_length[20]|callback__check_nik');
        $this->form_validation->set_rules('position', 'Position', 'trim|required|max_length[255]');
        if ($this->form_validation->run()) {
            $result = $this->profile_model->updateCompanyInfo(
                $userId, $this->input->post('companyId', TRUE), $this->input->post('nik', TRUE), $this->input->post('position', TRUE)
            );
            if ($result) {
                $this->load->model('users/user_model');
                $this->user_model->delete_cache('*');
                Events::trigger('backend::profile:companyinfo_updated', $userId);
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

    public function _check_nik($nik = '')
    {
        $userId = $this->the_auth_backend->getUserLoginId();
        if ($this->profile_model->with_trashed()->check_unique_field('nik', $nik, $userId, 'user_id')) {
            $this->form_validation->set_message('_check_nik', sprintf(lang('profile::msg:nik_already_exist'), $nik));
            return false;
        }

        return true;
    }

    public function saveaccount()
    {
        $userId = $this->the_auth_backend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::saving_failed')
            ]);
            return false;
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('email', 'Email', 'trim|required|max_length[100]|valid_email|callback__check_email');
        $changePassword = (bool)$this->input->post('changePassword');
        if ($changePassword) {
            $passMinLength = $this->the_auth_backend->getMinPasswordLength();
            $passMaxLength = $this->the_auth_backend->getMaxPasswordLength();
            $this->form_validation->set_rules('oldPassword', 'Current password', 'trim|required|callback__check_old_password');
            $this->form_validation->set_rules('newPassword', 'New password', 'trim|required|min_length[' . $passMinLength . ']|max_length[' . $passMaxLength . ']');
            $this->form_validation->set_rules('newPasswordConfirm', 'Confirm new password', 'trim|required|matches[newPassword]');

        }
        if ($this->form_validation->run()) {
            $data = [
                'email' => $this->input->post('email', true),
                'telegram_user' => $this->input->post('telegramUser', true),
            ];

            $result = $this->the_auth_backend->updateAccount($userId, $data, $changePassword, $this->input->post('newPassword'));
            if ($result) {
                $this->load->model('users/user_model');
                $this->user_model->delete_cache('*');
                Events::trigger('backend::profile:accountinfo_updated', $userId);
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

    public function _check_email($email = '')
    {
        $userId = $this->the_auth_backend->getUserLoginId();
        if ($this->user_model->with_trashed()->check_unique_field('email', $email, $userId, 'id')) {
            $this->form_validation->set_message('_check_email', sprintf(lang('profile::msg:email_already_exist'), $email));
            return false;
        }

        return true;
    }

    public function _check_old_password($password = '')
    {
        if (!empty($password)) {
            $userId = $this->the_auth_backend->getUserLoginId();

            if (!$this->the_auth_backend->hashPasswordDb($userId, $password)) {
                $this->form_validation->set_message('_check_old_password', lang('profile::msg:wrong_old_password'));
                return false;
            }
        }
        return true;
    }
}