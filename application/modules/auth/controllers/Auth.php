<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Auth
 *
 * @property User_model $user_model
 * @property Company_model $company_model
 * @property Department_model $department_model
 * @property Informer_model $informer_model
 */
class Auth extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->lang->load('auth/auth');
        $this->template->title('Auth');
    }

    // login page
    public  function login()
    {
        if (frontendIsLoggedIn()) {
            redirect('account');
        }

        $this->lang->load('auth/auth');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('user_login', 'lang:auth::identity', 'required');
        $this->form_validation->set_rules('user_password', 'lang:auth::password', 'required');
        $this->form_validation->set_error_delimiters('', '');
        if ($this->form_validation->run())
        {
            if ($this->_valid_csrf_nonce() === FALSE) 
            {
                $this->session->set_flashdata('message.error', lang('msg::request_failed'));
                // redirect('login', 'refresh');
            } 
            else {
                $remember = (bool)$this->input->post('remember');
                $userLogin = $this->input->post('user_login', true);
                $password = $this->input->post('user_password', true);
                if ($this->the_auth_frontend->doLogin($userLogin, $password, $remember)) 
                {
                    $this->session->set_flashdata('message.success', $this->the_auth_frontend->getMessageStr());
                    redirect('account');
                } 
                else {
                    $this->session->set_flashdata('message.error', $this->the_auth_frontend->getErrorStr('', '<br/>'));
                    redirect('login', 'refresh');
                }
            }
        }

        $this->template
            ->set_layout('auth_layout')
            ->pageTitle('Login')
            ->set('error_msg', $this->session->flashdata('message.error'))
            ->set('success_msg', $this->session->flashdata('message.success'))
            ->set('csrf', $this->_get_csrf_nonce())
            ->append_js('webpack::dist/page.login.js', true, 'page')
            ->build('auth/frontend_login');
    }

    public function join()
    {
        $this->load->library('form_validation');

        $this->form_validation->CI = &$this;
        $passMinLength = $this->the_auth_frontend->getMinPasswordLength();
        $passMaxLength = $this->the_auth_frontend->getMaxPasswordLength();

        $this->form_validation->set_rules('fullName', 'lang:frontend::auth:full_name', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('phone', 'lang:frontend::auth:phone', 'trim|required|max_length[15]');
        $this->form_validation->set_rules('email', 'lang:frontend::auth:email', 'trim|required|max_length[100]|valid_email|callback__check_email');
        $this->form_validation->set_rules('password', 'lang:frontend::auth:password', 'trim|required|min_length[' . $passMinLength . ']|max_length[' . $passMaxLength . ']');
        $this->form_validation->set_rules('passowrdConfirm', 'lang:frontend::auth:confirm_password', 'trim|required|matches[password]');
        $this->form_validation->set_rules('nik', 'lang:frontend::auth:nik', 'trim|required|max_length[20]|callback__check_nik');
        $this->form_validation->set_rules('positionName', 'lang:frontend::auth:position', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('companyId', 'lang:frontend::auth:company', 'trim|required');
        $this->form_validation->set_rules('companyBranchId', 'Location', 'trim|required');
        $this->form_validation->set_rules('departmentId', 'lang:frontend::auth:department', 'trim|required');
        $this->form_validation->set_error_delimiters('','');
        if ($this->input->post('departmentId') < 0) {
            $this->form_validation->set_rules('departmentOther', 'Others department', 'trim|required|max_length[100]');
        }
        if ($this->form_validation->run()) 
        {
            $data = [
                'full_name' => $this->input->post('fullName', true),
                'phone' => $this->input->post('phone', true),
                // 'email' => $this->input->post('email', true),
                // 'password' => $this->input->post('email', true),
                'nik' => $this->input->post('nik', true),
                'position' => $this->input->post('positionName', true),
                'company_id' => $this->input->post('companyId'),
                'company_branch_id' => $this->input->post('companyBranchId'),
                'company_other' => '',
                'department_id' => $this->input->post('departmentId'),
                'department_other' => $this->input->post('departmentOther', true)
            ];

            $register = $this->the_auth_frontend->register(
                '',
                $this->input->post('password', true),
                $this->input->post('email', true),
                $this->input->post('companyId', true),
                null,
                $data,
                'en',
                TRUE
            );
            if (!$register) {
                $this->session->set_flashdata('message.error', $this->the_auth_frontend->getMessageStr());
                redirect('join', 'refresh');
            } else {
                $this->session->set_flashdata('message.success', $this->the_auth_frontend->getMessageStr());

                if ($this->the_auth_frontend->doLogin($this->input->post('email', true), $this->input->post('password'), false)) {
                    $this->session->set_flashdata('message.success', $this->the_auth_frontend->getMessageStr());
                    redirect('account#/personalinfo');
                } else {
                    $this->session->set_flashdata('message.error', $this->the_auth_frontend->getErrorStr('', '<br/>'));
                    redirect('login', 'refresh');
                }
            }
        }

        $errorObject = sprintf(
            "ufhy.errorMessage = {" .
                "fullName: '%s',phone: '%s',email: '%s',password: '%s',passowrdConfirm: '%s',nik: '%s',positionName: '%s'," .
                "companyId: '%s',companyBranchId: '%s',departmentId: '%s',departmentOther: '%s',
            };",
            form_error('fullName'), form_error('phone'), form_error('email'), form_error('password'), form_error('passowrdConfirm'),
            form_error('nik'), form_error('positionName'), form_error('companyId'),
            form_error('companyBranchId'), form_error('departmentId'),
            form_error('departmentOther')
        );
        $rowObject = sprintf(
            "ufhy.rowItem = {" .
                "fullName: '%s',phone: '%s',email: '%s',password: '%s',passowrdConfirm: '%s',nik: '%s',positionName: '%s'," .
                "companyId: '%s',companyBranchId: '%s',departmentId: '%s',departmentOther: '%s',
            };",
            set_value('fullName'),
            set_value('phone'),
            set_value('email'),
            set_value('password'),
            set_value('passwordConfirm'),
            set_value('nik'),
            set_value('positionName'),
            set_value('companyId'),
            set_value('companyBranchId'),
            set_value('departmentId'),
            set_value('departmentOther')
        );
        ci()->template->append_metadata(
            '<script>'. $errorObject .''. $rowObject .'</script>'
        );
        $this->template
            ->append_js('webpack::dist/page.registration.js', true, 'page-vue')
            ->set('csrf', $this->_get_csrf_nonce())
            ->build('auth/frontend_registration');
    }

    public function _check_email($email = '')
    {
        if (!empty($email)) 
        {
            $this->load->model('users/user_model');
            if ($this->user_model->with_trashed()->check_unique_field('email', $email, $this->input->post('user_id'))) 
            {
                $this->form_validation->set_message('_check_email', sprintf(lang('frontend::auth:msg:email_already_exist'), $email));
                return false;
            }

            $this->load->model('references/email_list_model');
            $find = $this->email_list_model->count_rows(['email' => $email]);
            if ($find <= 0) {
                $this->form_validation->set_message('_check_email', 'Email not found in list email system');
                return false;
            }
        }

        return true;
    }

    public function _check_nik($nik = '')
    {
        if (!empty($nik)) 
        {
            $this->load->model('informer/informer_model');
            if ($this->informer_model->with_trashed()->check_unique_field('nik', $nik, $this->input->post('user_id'), 'user_id')) 
            {
                $this->form_validation->set_message('_check_nik', sprintf(lang('frontend::auth:msg:nik_already_exist'), $nik));
                return false;
            }
        }

        return true;
    }

    public function logout()
    {
        $this->the_auth_frontend->doLogout();
        $this->session->set_flashdata('message.success', $this->the_auth_frontend->getMessageStr());

        redirect();
    }

    public function forgot_password()
    {
        if (frontendIsLoggedIn()) {
            $this->session->set_flashdata('message.error', lang('auth::already_logged_in'));
            redirect('account');
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->form_validation->set_rules('login_reset', 'lang:auth::reset_identity', 'trim|required|valid_email');
        if ($this->form_validation->run()) 
        {
            if ($this->_valid_csrf_nonce() === false) 
            {
                $this->session->set_flashdata('message.error', lang('msg::request_failed'));
            } 
            else { 
                $identity = $this->input->post('login_reset');
                $forgottenPassword = $this->the_auth_frontend->forgottenPassword($identity);
                if ($forgottenPassword) {
                    $this->session->set_flashdata('message.success', $this->the_auth_frontend->getMessageStr());
                } else {
                    $this->session->set_flashdata('message.error', $this->the_auth_frontend->getErrorStr('', '<br/>'));
                }
            }

            redirect('auth/forgot-password', 'refresh');
        }

        $this->template
            ->set_layout('auth_layout')
            ->pageTitle('Forgotten Password')
            ->set('error_msg', $this->session->flashdata('message.error'))
            ->set('success_msg', $this->session->flashdata('message.success'))
            ->set('csrf', $this->_get_csrf_nonce())
            ->append_js('webpack::dist/page.login.js', true, 'page')
            ->build('auth/frontend_forgotten_password');
    }

    public function reset_password($code = NULL)
    {
        if (!$code) {
            show_error('Missing some parameters');
        }

        $check = $this->the_auth_frontend->forgottenPasswordCheck($code);
        if ($check) {
            $this->load->library('form_validation');
            $this->form_validation->CI = &$this;

            $passMinLength = $this->the_auth_frontend->getMinPasswordLength();
            $passMaxLength = $this->the_auth_frontend->getMaxPasswordLength();
            $this->form_validation->set_rules('identityPassword', 'lang:auth::password_new', 'trim|required|min_length[' . $passMinLength . ']|max_length[' . $passMaxLength . ']');
            $this->form_validation->set_rules('identityPasswordConfirm', 'lang:auth::password_new_confirm', 'trim|required|matches[identityPassword]');
            if ($this->form_validation->run())
            {
                if ($check->id !== $this->input->post('userId'))
                {
                    $this->session->set_flashdata('message.error', lang('auth::reset_password_error_csrf'));
                    redirect('auth/reset-password/' . $code, 'refresh');
                }
                else {
                    $change = $this->the_auth_frontend->resetPassword($check->id, $this->input->post('identityPassword'));
                    if ($change) {
                        $this->session->set_flashdata('message.success', $this->the_auth_frontend->getMessageStr());
                        redirect("login", 'refresh');
                    } else {
                        $this->session->set_flashdata('message.error', $this->the_auth_frontend->getErrorStr());
                        redirect('auth/reset-password/' . $code, 'refresh');
                    }
                }
            }

            $this->template
                ->set_layout('auth_layout')
                ->pageTitle('Reset Password')
                ->set('error_msg', $this->session->flashdata('message.error'))
                ->set('success_msg', $this->session->flashdata('message.success'))
                ->set('csrf', $this->_get_csrf_nonce())
                ->set('userId', $check->id)
                ->append_js('webpack::dist/page.login.js', true, 'page')
                ->build('auth/frontend_reset_password');
        }
        else {
            $this->session->set_flashdata('message.error', $this->the_auth_frontend->getErrorStr('', '<br/>'));
            redirect('auth/forgot-password');
        }
    }

    public function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    public function _valid_csrf_nonce()
    {
        $csrfkey = $this->input->post($this->session->flashdata('csrfkey'));

        if ($csrfkey && $csrfkey == $this->session->flashdata('csrfvalue')) {
            return true;
        } else {
            return false;
        }
    }
}