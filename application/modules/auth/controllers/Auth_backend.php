<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Auth
 *
 * @property User_model $user_model
 * @property The_auth_backend $the_auth_backend
 */
class Auth_backend extends Backend_Controller
{
    public $_themeName = 'backend-auth';

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('auth/auth');
        $this->template->title('Auth');
    }

    // login default page
    public function index()
    {
        $this->login();
    }

    // login page
    public  function login()
    {
        $redirectKey = $this->config->item('back_auth_redirect_key');
        $redirectUri = $this->session->userdata($redirectKey);

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('user_login', 'lang:auth::identity', 'required');
        $this->form_validation->set_rules('user_password', 'lang:auth::password', 'required');
        if ($this->form_validation->run())
        {
            $remember = (bool)$this->input->post('remember');
            $userLogin = $this->input->post('user_login', TRUE);
            $password = $this->input->post('user_password', TRUE);
            if ($this->the_auth_backend->doLogin($userLogin, $password, $remember))
            {
                $this->session->set_flashdata('message.success', $this->the_auth_backend->getMessageStr());
                $redirect = $this->input->post('redirect', TRUE);
                if ($redirect) {
                    header('Location: https://' . $redirect);
                    return false;
                }
                else {
                    redirect($redirectUri ? $redirectUri : 'acp');
                }
            }
            else {
                $this->session->set_flashdata('message.error', $this->the_auth_backend->getErrorStr('', '<br/>'));
                redirect('acp/auth', 'refresh');
            }
        }

        $this->template
            ->pageTitle('Login')
            ->set('error_msg', $this->session->flashdata('message.error'))
            ->set('success_msg', $this->session->flashdata('message.success'))
            ->build('auth/backend_login');
    }

    public function logout()
    {
        $this->the_auth_backend->doLogout();
        $this->session->set_flashdata('message.success', $this->the_auth_backend->getMessageStr());

        redirect('acp/auth');
    }

    public function forgotten_password()
    {
        if (backendIsLoggedIn()) {
            $this->session->set_flashdata('message.error', lang('auth::already_logged_in'));
            redirect(BACKEND_URLPREFIX);
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->form_validation->set_rules('login_reset', 'lang:auth::reset_identity', 'trim|required|valid_email');
        if ($this->form_validation->run()) 
        {
            if ($this->_valid_csrf_nonce() === FALSE)
            {
                $this->session->set_flashdata('message.error', lang('msg::request_failed'));
            }
            else {
                $identity = $this->input->post('login_reset');
                $forgottenPassword = $this->the_auth_backend->forgottenPassword($identity);
                if ($forgottenPassword) {
                    $this->session->set_flashdata('message.success', $this->the_auth_backend->getMessageStr());
                } else {
                    $this->session->set_flashdata('message.error', $this->the_auth_backend->getErrorStr('', '<br/>'));
                }
            }

            redirect(BACKEND_URLPREFIX  . '/auth/forgotten_password', 'refresh');
        }

        $this->template->pageTitle('Forgotten password')
            ->set('error_msg', $this->session->flashdata('message.error'))
            ->set('success_msg', $this->session->flashdata('message.success'))
            ->set('csrf', $this->_get_csrf_nonce())
            ->build('auth/backend_forgotten_password');
    }

    public function reset_password($code = NULL)
    {
        if (!$code) {
            show_error('Missing some parameters');
        }

        $check = $this->the_auth_backend->forgottenPasswordCheck($code);
        if ($check) {
            $this->load->library('form_validation');
            $this->form_validation->CI = &$this;

            $passMinLength = $this->the_auth_backend->getMinPasswordLength();
            $passMaxLength = $this->the_auth_backend->getMaxPasswordLength();
            $this->form_validation->set_rules('identityPassword', 'lang:auth::password_new', 'trim|required|min_length[' . $passMinLength . ']|max_length[' . $passMaxLength . ']');
            $this->form_validation->set_rules('identityPasswordConfirm', 'lang:auth::password_new_confirm', 'trim|required|matches[identityPassword]');
            if ($this->form_validation->run())
            {
                if ($check->id !== $this->input->post('userId'))
                {
                    $this->session->set_flashdata('message.error', lang('auth::reset_password_error_csrf'));
                    redirect(BACKEND_URLPREFIX . '/auth/reset_password/' . $code, 'refresh');
                }
                else {
                    $change = $this->the_auth_backend->resetPassword($check->id, $this->input->post('identityPassword'));
                    if ($change) {
                        $this->session->set_flashdata('message.success', $this->the_auth_backend->getMessageStr());
                        redirect(BACKEND_URLPREFIX . "/auth", 'refresh');
                    } else {
                        $this->session->set_flashdata('message.error', $this->the_auth_backend->getErrorStr());
                        redirect(BACKEND_URLPREFIX . '/auth/reset_password/' . $code, 'refresh');
                    }
                }
            }

            $this->template
                ->pageTitle('Reset Password')
                ->set('error_msg', $this->session->flashdata('message.error'))
                ->set('success_msg', $this->session->flashdata('message.success'))
                ->set('csrf', $this->_get_csrf_nonce())
                ->set('userId', $check->id)
                ->build('auth/backend_reset_password');
        }
        else {
            $this->session->set_flashdata('message.error', $this->the_auth_backend->getErrorStr('', '<br/>'));
            redirect('auth/forgot-password');
        }
    }

    public function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('backend_csrfkey', $key);
        $this->session->set_flashdata('backend_csrfvalue', $value);

        return array($key => $value);
    }

    public function _valid_csrf_nonce()
    {
        $csrfkey = $this->input->post($this->session->flashdata('backend_csrfkey'));

        if ($csrfkey && $csrfkey == $this->session->flashdata('backend_csrfvalue')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}