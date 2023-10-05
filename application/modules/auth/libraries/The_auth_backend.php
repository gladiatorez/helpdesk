<?php defined('BASEPATH') OR exit('No direct script access allowed');

class The_auth_backend
{
	protected $_ci;

    protected $_config = [];
	protected $_errors = [];
	protected $_messages = [];

    protected $_userModel;
    protected $_profileModel;
    protected $_groupModel;
    protected $_groupPermissionsModel;
    protected $_loginAttemptsModel;

    protected $_storeSalt = true;
    protected $_storeSaltLength = 6;
    protected $_loginIdentity = 'email';
    protected $_loginIdentityCookie = 'loginIdentityCookie';
    protected $_maxLoginAttempts = 0;
    protected $_rememberMeExpire = 0;
    protected $_rememberMeCookieName = 'rememberMeCookieName';
    protected $_recheckTimer = 0;
    protected $_extendOnLogin = false;

    protected $_minPasswordLength = 5;
    protected $_maxPasswordLength = 10;

    protected $_forgottenPasswordCode;
    protected $_forgottenPasswordExpired = 0;

    protected $_cacheUser = [];

	public function __construct()
	{
		$this->_ci =& get_instance();
        $this->_ci->load->config('auth/the_auth_backend', TRUE);
        $this->_ci->load->helper('cookie');
        $this->_ci->load->helper('auth/the_auth_backend');
        $this->_ci->lang->load('auth/auth');
        $this->_initConfig();

		$this->_ci->load->model($this->_config['models']['user_users'])
            ->model($this->_config['models']['user_profiles'])
            ->model($this->_config['models']['user_groups'])
            ->model($this->_config['models']['user_groups_permissions'])
            ->model($this->_config['models']['user_login_attempts']);
	}

	protected function _initConfig()
    {
        $this->_config = $this->_ci->config->config['the_auth_backend'];

        $this->_userModel = basename($this->_config['models']['user_users']);
        $this->_profileModel = basename($this->_config['models']['user_profiles']);
        $this->_groupModel = basename($this->_config['models']['user_groups']);
        $this->_groupPermissionsModel = basename($this->_config['models']['user_groups_permissions']);
        $this->_loginAttemptsModel = basename($this->_config['models']['user_login_attempts']);

        $this->_storeSalt = (bool) Setting::get('auth_store_salt', 'backend_auth');
        $this->_storeSaltLength = Setting::get('auth_store_salt_length', 'backend_auth');
        $this->_loginIdentity = Setting::get('auth_login_identity', 'backend_auth');
        $this->_loginIdentityCookie = Setting::get('auth_identity_cookie', 'backend_auth');
        $this->_maxLoginAttempts = Setting::get('auth_max_login_attempts', 'backend_auth');
        $this->_rememberMeExpire = Setting::get('auth_remember_me_expire', 'backend_auth');
        $this->_rememberMeCookieName = Setting::get('auth_remember_me_cookie', 'backend_auth');
        $this->_recheckTimer = Setting::get('auth_recheck_timer', 'backend_auth');
        $this->_extendOnLogin = (bool) Setting::get('auth_extend_on_login', 'backend_auth');
        if ((int) Setting::get('auth_min_password_length', 'backend_auth') >= 5) {
            $this->_minPasswordLength = (int) Setting::get('auth_min_password_length', 'backend_auth');
        }
        $this->_maxPasswordLength = (int) Setting::get('auth_max_password_length', 'backend_auth');
        $this->_forgottenPasswordExpired = (int)Setting::get('auth_forgot_password_expired', 'backend_auth');
    }

    /**
     * Set message success
     *
     * @param $message
     * @param bool $usingLang
     * @return $this
     */
	protected function setMessage($message, $usingLang = false)
	{
		$this->_messages[] = $usingLang ? $this->_ci->lang->line($message) : $message;
		return $this;
	}

    /**
     * get message success as array
     * @return array
     */
	public function getMessageArr()
	{
		return $this->_messages;
	}

    /**
     * get message success as string
     * @param string $startDelimiter
     * @param string $endDelimiter
     * @return string
     */
	public function getMessageStr($startDelimiter = '', $endDelimiter = '')
	{
		$_output = '';
		foreach ($this->_messages as $message) {
			$_output .= $startDelimiter . $message . $endDelimiter;
		}
		return $_output;
	}

    /**
     * set error message
     * @param $message
     * @param bool $usingLang
     * @return $this
     */
	protected function setError($message, $usingLang = FALSE)
	{
        $this->_errors[] = $usingLang ? $this->_ci->lang->line($message) : $message;
		return $this;
	}

    /**
     * Get error message as array
     * @return array
     */
	public function getErrorArr()
	{
		return $this->_errors;
	}

    /**
     * Get Error message as String
     * @param string $startDelimiter
     * @param string $endDelimiter
     * @return string
     */
	public function getErrorStr($startDelimiter = '', $endDelimiter = '')
	{
		$_output = '';
		foreach ($this->_errors as $error) {
			$_output .= $startDelimiter . $error . $endDelimiter;
		}
		return $_output;
	}

    /**
     * get default min length password
     * @return int
     */
    public function getMinPasswordLength()
    {
        return $this->_minPasswordLength;
    }

    /**
     * get default max length password
     * @return int
     */
	public function getMaxPasswordLength()
    {
	    return $this->_maxPasswordLength;
    }

    /**
     * get default login method username || email
     * @return string
     */
    public function getLoginIdentity()
    {
        return $this->_loginIdentity;
    }

    /**
     * get salt of password
     * @return bool|string
     */
    protected function getSalt()
    {
        $raw_salt_len = 16;

        $buffer = '';
        $buffer_valid = FALSE;

        if (function_exists('random_bytes'))
        {
            $buffer = random_bytes($raw_salt_len);
            if ($buffer)
            {
                $buffer_valid = TRUE;
            }
        }

        if (!$buffer_valid && function_exists('mcrypt_create_iv') && !defined('PHALANGER'))
        {
            $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
            if ($buffer)
            {
                $buffer_valid = TRUE;
            }
        }

        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes'))
        {
            $buffer = openssl_random_pseudo_bytes($raw_salt_len);
            if ($buffer)
            {
                $buffer_valid = TRUE;
            }
        }

        if (!$buffer_valid && @is_readable('/dev/urandom'))
        {
            $f = fopen('/dev/urandom', 'r');
            $read = strlen($buffer);
            while ($read < $raw_salt_len)
            {
                $buffer .= fread($f, $raw_salt_len - $read);
                $read = strlen($buffer);
            }
            fclose($f);
            if ($read >= $raw_salt_len)
            {
                $buffer_valid = TRUE;
            }
        }

        if (!$buffer_valid || strlen($buffer) < $raw_salt_len)
        {
            $bl = strlen($buffer);
            for ($i = 0; $i < $raw_salt_len; $i++)
            {
                if ($i < $bl)
                {
                    $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                }
                else
                {
                    $buffer .= chr(mt_rand(0, 255));
                }
            }
        }

        $salt = $buffer;

        // encode string with the Base64 variant used by crypt
        $base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        $bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $base64_string = base64_encode($salt);
        $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);

        $salt = substr($salt, 0, $this->_storeSaltLength);

        return $salt;
    }

    /**
     * hash password
     * @param $password
     * @param bool $salt
     * @return bool|mixed|string
     */
    public function hashPassword($password, $salt = false)
    {
        if (empty($password)) {
            return false;
        }

        // original decrypt by starter
        $password = $salt ? $password . $salt : $password;
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * hash password with password db
     * @param $userId
     * @param $password
     * @return bool
     */
    public function hashPasswordDb($userId, $password)
    {
        $passwordDb = $this->_ci->{$this->_userModel}->fields('password,salt')
            ->get(['id' => $userId]);
        if (!$passwordDb) {
            return false;
        }

        $password = $password . $passwordDb->salt;
        return password_verify($password, $passwordDb->password);
    }

	public function usernameCheck($identity)
    {
        return $this->_ci->{$this->_userModel}->identityCheck($identity, 'username');
    }

    public function emailCheck($identity)
    {
        return $this->_ci->{$this->_userModel}->identityCheck($identity, 'email');
    }

    /**
     * get user login
     * @param null $userId
     * @return bool
     */
    public function getUserLogin($userId = null)
    {
        $userId = isset($userId) ? $userId : $this->_ci->session->userdata('user_id');

        if (!$userId) {
            return false;
        }

        if (isset($this->_cacheUser[$userId])) 
        {
            return $this->_cacheUser[$userId];
        }

        $user = ci()->{$this->_userModel}
            ->set_cache('current_user_'.$userId)
            ->get(['id' => $userId]);
        
        if ($user) 
        {
            $group = ci()->{$this->_groupModel}->set_cache('current_user_group_'.$userId)
                ->get(['id' => $user->group_id]);
            $profile = ci()->{$this->_profileModel}->set_cache('current_user_profile_'.$userId)
                ->get(['user_id' => $user->id]);

            $user->group = $group ? $group : null;
            $user->profile = $profile ? $profile : null;
        }

        // $user = $this->_ci->{$this->_userModel}
        //     ->set_cache('current_user_'.$userId)
        //     ->fields('id,ip_address,username,telegram_user,email,activation_code,forgotten_password_code,forgotten_password_time,remember_code,active,lang,group_id,last_login,company_id')
        //     ->with('profile', [ 'fields:full_name,phone,photo_file,updated_at,nik,position'])
        //     ->with('group', ['fields:id,name,is_admin,is_helpdesk'])
        //     ->get(['id' => $userId]);
        // $this->_ci->db->reset_query();
        // return $user;

        $this->_cacheUser[$userId] = $user;
        return $this->_cacheUser[$userId];
    }

    /**
     * get user login id
     * @return mixed|null
     */
    public function getUserLoginId()
    {
        $userId = $this->_ci->session->userdata('user_id');
        if (!empty($userId)) {
            return $userId;
        }

        return null;
    }

    /**
     * is user admin
     * @param null $userId
     * @return bool
     */
    public function isUserAdmin($userId = null)
    {
        $user = $this->getUserLogin($userId);
        if (!$user) {
            return false;
        }

        return (bool) $user->group->is_admin;
    }

    /**
     * is user helpdesk
     * @param null $userId
     * @return bool
     */
    public function isUserHelpdek($userId = null)
    {
        $user = $this->getUserLogin($userId);
        if (!$user) {
            return false;
        }

        return (bool) $user->group->is_helpdesk;
    }

	public function register($username, $password, $email, $company_id, $groupId = null, $profile = [], $lang = 'id', $active = FALSE)
    {
        if ($this->_loginIdentity === 'username' && $this->usernameCheck($username)) {
            $this->setError('auth::duplicate_username', true);
            return false;
        }

        if ($this->emailCheck($email)) {
            $this->setError('auth::duplicate_email', TRUE);
            return false;
        }

        if (!array_key_exists('full_name' , $profile)) {
            $this->setError('auth::full_name_required', TRUE);
            return false;
        }

        if ($this->_loginIdentity != 'username') {
            if (empty($username)) {
                $fullNameArr = explode(' ', $profile['full_name']);
                $firstPart = strtolower($fullNameArr[0]);
                $secondPart = '';
                if (isset($fullNameArr[1])) {
                    $secondPart = strtolower($fullNameArr[1]);
                }
                $username = trim($firstPart) . trim($secondPart);
            }

            $originalUsername = $username;
            for ($i = 0; $this->usernameCheck($username); $i++) {
                if ($i > 0) {
                    $username = $originalUsername . $i;
                }
            }
        }

        if (!$groupId) {
            $group = $this->_ci->{$this->_groupModel}->getDefaultGroup();
            if (!$group) {
                $this->setError('auth::default_group_not_set', TRUE);
                return false;
            }

            $groupId = $group->id;
        }

        $salt = $this->_storeSalt ? $this->getSalt() : false;
        $password = $this->hashPassword($password, $salt);

        // data user
        $user = [
            'ip_address' => $this->_ci->input->ip_address(),
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'lang' => $lang,
            'group_id' => $groupId,
            'company_id' => $company_id,
            'active' => $active
        ];

        if ($this->_storeSalt) {
            $user['salt'] = $salt;
        }

        $register = $this->_ci->{$this->_userModel}->create($user, $profile);
        if ($register) {
            $this->setMessage('auth::register_successful', TRUE);
        } else {
            $this->setError('auth::register_successful', TRUE);
            return false;
        }

        return $register;
    }

    public function isMaxLoginAttemptsExceeded($identity, $time = 600)
    {
        if ($this->_maxLoginAttempts > 0) {
            $attempts = $this->_ci->{$this->_loginAttemptsModel}->getAttemptsNum($identity, $time);
            return $attempts >= $this->_maxLoginAttempts;
        }
        return false;
    }

    public function increaseLoginAttempts($identity)
    {
        $data = [
            'ip_address' => $this->_ci->input->ip_address(),
            'login' => $identity,
            'time' => time()
        ];

        return $this->_ci->{$this->_loginAttemptsModel}->insert($data);
    }

    public function clearLoginAttempts($identity, $oldAttemptsExpirePeriod = 86400, $time = 600)
    {
        return $this->_ci->{$this->_loginAttemptsModel}->clearLoginAttempts($identity, $oldAttemptsExpirePeriod, $time);
    }

    public function setUserSession($user)
    {
        $sessionData = [
            'identity'              => $user->{$this->_loginIdentity},
            $this->_loginIdentity   => $user->{$this->_loginIdentity},
            'email'                 => $user->email,
            'user_id'               => $user->id,
            'old_last_login'        => $user->last_login,
            'last_check'            => time()
        ];
        $this->_ci->session->set_userdata($sessionData);
        return true;
    }

    public function updateUserLastLogin($userId)
    {
        $this->_ci->load->helper('date');
        return $this->_ci->{$this->_userModel}->update(
            ['last_login' => date('Y-m-d H:i:s')], ['id' => $userId]
        );
    }

    protected function rememberUser($userId)
    {
        if (!$userId) {
            return false;
        }

        $user = $this->_ci->{$this->_userModel}->get(['id' => $userId]);
        if (!$user) {
            return false;
        }

        $salt = $this->getSalt();
        if ( $this->_ci->{$this->_userModel}->update(['remember_code' => $salt], ['id' => $userId]) )
        {
            $expire = $this->_rememberMeExpire;
            set_cookie($this->_loginIdentityCookie, $user->{$this->_loginIdentity}, $expire);
            set_cookie($this->_rememberMeCookieName, $salt, $expire);

            return true;
        }

        return false;
    }

    public function rememberUserLogin()
    {
        // check for valid data
        if (!get_cookie($this->_loginIdentityCookie) ||
            !get_cookie($this->_rememberMeCookieName) ||
            !$this->_ci->{$this->_userModel}->identityCheck(get_cookie($this->_loginIdentityCookie), $this->_loginIdentity))
        {
            return false;
        }

        $user = $this->_ci->{$this->_userModel}->fields('username,email,id,last_login')
            ->get([
                $this->_loginIdentity => urldecode(get_cookie($this->_loginIdentityCookie)),
                'remember_code' => get_cookie($this->_rememberMeCookieName),
                'active' => 1
            ]);
        if ($user)
        {
            $this->updateUserLastLogin($user->id);
            $this->setUserSession($user);
            if ($this->_extendOnLogin) {
                $this->rememberUser($user->id);
            }

            $this->_regenerateSession();
            return true;
        }
        return false;
    }

    /**
     * do login
     * @param $identity
     * @param $password
     * @param bool $rememberMe
     * @return bool
     */
    public function doLogin($identity, $password, $rememberMe = false)
    {
        if (empty($identity) || empty($password)) {
            if ($this->_loginIdentity === 'username') {
                $this->setError('auth::login_username_password_empty', TRUE);
            } else {
                $this->setError('auth::login_email_password_empty', TRUE);
            }
            return false;
        }

        if ($this->isMaxLoginAttemptsExceeded($identity))
        {
            $this->setError('auth::login_timeout', TRUE);
            return false;
        }
        
        // get user group can access control panel
        $groups = $this->_ci->{$this->_groupModel}
            ->where('is_admin', TRUE)
            ->where('view_cp', TRUE, NULL, TRUE)
            ->get_all();
        if (!$groups) {
            $this->setError('auth::login_unsuccessful', TRUE);
            return false;
        }
        $groupIds = [];
        foreach ($groups as $group) {
            if (!array_key_exists($group->id, $groupIds)) {
                array_push($groupIds, $group->id);
            }
        }

        $user = $this->_ci->{$this->_userModel}->where('group_id', $groupIds)
            ->get([ $this->_loginIdentity => $identity ]);
        if ($user) {
            $password = $this->hashPasswordDb($user->id, $password);
            if ($password) {
                if ($user->active == 0) {
                    $this->setError('auth::login_unsuccessful_not_active');
                    return false;
                }

                $this->setUserSession($user);
                $this->updateUserLastLogin($user->id);
                $this->clearLoginAttempts($identity);

                if ($rememberMe) {
                    $this->rememberUser($user->id);
                }

                $this->_regenerateSession();
                $this->setMessage('auth::login_successful', TRUE);
                return true;
            }
        }

        $this->increaseLoginAttempts($identity);
        $this->setError('auth::login_unsuccessful', TRUE);
        return false;
    }

    public function doLoginTelegram($email, $password, $telegramUser)
    {
        // get user group can access control panel
        $groups = $this->_ci->{$this->_groupModel}
            ->where('is_admin', TRUE)
            ->where('view_cp', TRUE, NULL, TRUE)
            ->get_all();
        if (!$groups) {
            return false;
        }

        $groupIds = [];
        foreach ($groups as $group) {
            if (!array_key_exists($group->id, $groupIds)) {
                array_push($groupIds, $group->id);
            }
        }

        $user = $this->_ci->{$this->_userModel}->where('group_id', $groupIds)
            ->get([ 'email' => $email ]);
        if ($user) {
            $password = $this->hashPasswordDb($user->id, $password);
            if ($password) {
                if ($user->active == 0) {
                    return false;
                }

                $this->_ci->{$this->_userModel}->update(
                    ['telegram_user' => $telegramUser],
                    ['email' => $email]
                );
                return true;
            }
        }

        return false;
    }

    /**
     * do logout
     * @return bool
     */
    public function doLogout()
    {
        $userId = $this->_ci->session->userdata('user_id');
        $this->_ci->{$this->_userModel}->delete_cache('current_user_'.$userId);

        $this->_ci->session->unset_userdata(['identity', $this->_loginIdentity, 'email', 'user_id', 'old_last_login', 'last_check']);
        if (get_cookie($this->_loginIdentityCookie)) {
            delete_cookie($this->_loginIdentityCookie);
        }
        if (get_cookie($this->_rememberMeCookieName)) {
            delete_cookie($this->_rememberMeCookieName);
        }

        $this->_ci->session->sess_destroy();
        if (version_compare(PHP_VERSION, '7.0.0') >= 0)
        {
            session_start();
        }
        $this->_ci->session->sess_regenerate(TRUE);

        $this->setMessage('auth::logout_successful', TRUE);
        return true;
    }

    protected function reCheckSession()
    {
        $recheck = (NULL !== $this->_recheckTimer) ? $this->_recheckTimer : 0;
        if ($recheck !== 0)
        {
            $lastLogin = $this->_ci->session->userdata('last_check');
            if ($lastLogin + $recheck < time())
            {
                $user = $this->_ci->{$this->_userModel}->get([
                    $this->_loginIdentity => $this->_ci->session->userdata('identity'),
                    'active' => '1'
                ]);
                if ($user) {
                    $this->_ci->session->set_userdata('last_check', time());
                }
                else {
                    $this->_ci->session->unset_userdata(array($this->_loginIdentity, 'id', 'user_id'));
                }
            }
        }

        return $this->_ci->session->userdata('identity')
            && $this->_ci->session->userdata($this->_loginIdentity)
            && $this->_ci->session->userdata('user_id');
    }

    public function loggedIn()
    {
        $recheck = $this->reCheckSession();

        // auto-login the user if they are remembered
        if ( !$recheck && get_cookie($this->_loginIdentityCookie) && get_cookie($this->_rememberMeCookieName) )
        {
            $recheck = $this->rememberUserLogin();
        }

        return $recheck;
    }

    protected function _regenerateSession() {

        if (substr(CI_VERSION, 0, 1) == '2')
        {
            // Save sess_time_to_update and set it temporarily to 0
            // This is done in order to forces the sess_update method to regenerate
            $oldSessTimeToUpdate = $this->_ci->session->sess_time_to_update;
            $this->_ci->session->sess_time_to_update = 0;

            // Call the sess_update method to actually regenerate the session ID
            $this->_ci->session->sess_update();

            // Restore sess_time_to_update
            $this->_ci->session->sess_time_to_update = $oldSessTimeToUpdate;
        }
        else
        {
            $this->_ci->session->sess_regenerate(FALSE);
        }
    }

    /**
     * Change user password
     *
     * @param $userId
     * @param $password
     * @return bool
     */
    public function changeUserPassword($userId, $password)
    {
        $user = $this->getUserLogin($userId);
        if (!$user) {
            $this->setError('auth::user_not_exist', TRUE);
            return false;
        }

        $salt = $this->_storeSalt ? $this->getSalt() : false;
        $password = $this->hashPassword($password, $salt);
        $user = ['password' => $password, 'salt' => $salt];

        $update = $this->_ci->{$this->_userModel}->update($user, ['id' => $userId]);
        if (!$update) {
            $this->setError('auth::change_password_unsuccessful', TRUE);
            return false;
        }

        $this->setMessage('auth::change_password_successful', TRUE);
        return true;
    }

    public function deleteUser($userId)
    {
        $user = $this->getUserLogin($userId);
        if (!$user) {
            $this->setError('auth::user_not_exist', TRUE);
            return false;
        }

        if ($this->_ci->{$this->_userModel}->remove($userId)) {
            $this->setMessage('auth:user_remove_successful', TRUE);
            return true;
        }

        $this->setMessage('auth:user_remove_unsuccessful', TRUE);
        return false;
    }

    public function updateUser($userId, $userData, $profileData, $changePassword = false, $newPassword = '')
    {
        $user = $this->getUserLogin($userId);
        if (!$user) {
            $this->setError('auth::user_not_exist', TRUE);
            return false;
        }

        if ($changePassword) {
            $salt = $this->_storeSalt ? $this->getSalt() : false;
            $password = $this->hashPassword($newPassword, $salt);
            $userData['salt'] = $salt;
            $userData['password'] = $password;
        }

        if ($this->_ci->{$this->_userModel}->edit($userId, $userData, $profileData)) {
            $this->setMessage('auth:user_update_successful', TRUE);
            return true;
        }

        $this->setMessage('auth:user_update_unsuccessful', TRUE);
        return false;
    }

    public function updateAccount($userId, $userData, $changePassword, $newPassword)
    {
        $user = $this->getUserLogin($userId);
        if (!$user) {
            $this->setError('auth::user_not_exist', true);
            return false;
        }

        if ($changePassword) {
            $salt = $this->_storeSalt ? $this->getSalt() : false;
            $password = $this->hashPassword($newPassword, $salt);
            $userData['salt'] = $salt;
            $userData['password'] = $password;
        }

        if ($this->_ci->{$this->_userModel}->update($userData, ['id' => $userId])) {
            $this->setMessage('auth:user_update_successful', true);
            return true;
        }

        $this->setMessage('auth:user_update_unsuccessful', true);
        return false;
    }

    public function forgottenPassword($email)
    {
        if (empty($email)) {
            $this->setError('auth::forgot_password_unsuccessful', TRUE);
            return false;
        }

        // get group can send ticket
        $groups = $this->_ci->{$this->_groupModel}
            ->where('is_admin', true)
            ->where('view_cp', true, null, true)
            ->get_all();
        if (!$groups) {
            $this->setError('auth::forgot_password_unsuccessful', TRUE);
            return false;
        }
        $groupIds = [];
        foreach ($groups as $group) {
            if (!array_key_exists($group->id, $groupIds)) {
                array_push($groupIds, $group->id);
            }
        }

        $findUser = $this->_ci->{$this->_userModel}
            ->where('group_id', $groupIds)
            ->get(['email' => $email]);
        if (!$findUser) {
            $this->setError('auth::forgot_password_unsuccessful', TRUE);
            return false;
        }

        $activationCodePart = '';
        if (function_exists('openssl_random_pseudo_bytes')) {
            $activationCodePart = openssl_random_pseudo_bytes(128);
        }
        for ($i = 0; $i < 1024; $i++) {
            $activationCodePart = sha1($activationCodePart . mt_rand() . microtime());
        }

        $key = $this->hashPassword($activationCodePart.$email);
        if ($key != '' && $this->_ci->config->item('permitted_uri_chars') != '' && $this->_ci->config->item('enable_query_strings') == false) 
        {
            // preg_quote() in PHP 5.3 escapes -, so the str_replace() and addition of - to preg_quote() is to maintain backwards
            // compatibility as many are unaware of how characters in the permitted_uri_chars will be parsed as a regex pattern
            if (!preg_match("|^[" . str_replace(array('\\-', '\-'), '-', preg_quote($this->_ci->config->item('permitted_uri_chars'), '-')) . "]+$|i", $key)) 
            {
                $key = preg_replace("/[^" . $this->_ci->config->item('permitted_uri_chars') . "]+/i", "-", $key);
            }
        }

        $this->_forgottenPasswordCode = $key;
        $this->_ci->{$this->_userModel}->update([
            'forgotten_password_code' => $key,
            'forgotten_password_time' => time()
        ], ['email' => $email]);

        if ($this->_ci->db->affected_rows()) 
        {
            $data['slug']               = 'forgotten_password';
            $data['to']                 = $findUser->email;
            $data['from']               = Setting::get('server_email');
            $data['name']               = Setting::get('site_name_abbr');
            $data['reply-to']           = Setting::get('contact_email');
            $data['forgotten_code']     = $key;
            $data['user']               = $findUser;
            $data['url_forgotten']      = site_url_backend('auth/reset_password');
            $data['url_forgotten_code'] = site_url_backend('auth/reset_password/' . $key);

            $this->_ci->load->library('addons/the_email');
            $send = $this->_ci->the_email->send_email($data);
            if (!$send) {
                $this->setError('auth::forgot_password_unsuccessful', TRUE);
                return false;
            }

            $this->setMessage('auth::forgot_password_successful', TRUE);
            return true;
        }

        $this->setError('auth::forgot_password_unsuccessful', TRUE);
        return false;
    }

    public function clearForgottenPasswordCode($code)
    {
        if (empty($code)) {
            return false;
        }

        $count = $this->_ci->{$this->_userModel}->count_rows(['forgotten_password_code' => $code]);
        if ($count > 0) {
            $data = array(
                'forgotten_password_code' => null,
                'forgotten_password_time' => null
            );
            $this->_ci->{$this->_userModel}->update($data, ['forgotten_password_code' => $code]);

            return true;
        }

        return false;
    }

    public function forgottenPasswordCheck($code)
    {
        $user = $this->_ci->{$this->_userModel}->get(['forgotten_password_code' => $code]);
        if (!$user) {
            $this->setError('auth::forgot_password_check_unsuccessful', TRUE);
            return false;
        }

        if ($this->_forgottenPasswordExpired > 0) 
        {
            if (time() - $user->forgotten_password_time > $this->_forgottenPasswordExpired) 
            {
                $this->clearForgottenPasswordCode($code);
                $this->setError('auth::forgot_password_check_expired', TRUE);
                return false;
            }
        }

        return $user;
    }

    public function resetPassword($id, $newPassword)
    {
        if (empty($id) || empty($newPassword)) {
            $this->setError('auth::reset_password_unsuccessful', TRUE);
            return false;
        }

        $user = $this->_ci->{$this->_userModel}->fields('id,password,salt')->get(['id' => $id]);
        if (!$user) {
            $this->setError('auth::reset_password_unsuccessful');
            return false;
        }

        $newPassword = $this->hashPassword($newPassword, $user->salt);
        $data = array(
            'password'                  => $newPassword,
            'remember_code'             => null,
            'forgotten_password_code'   => null,
            'forgotten_password_time'   => null,
        );
        $this->_ci->{$this->_userModel}->update($data, ['id' => $id]);
        $return = $this->_ci->db->affected_rows() == 1;
        if ($return) {
            $this->setMessage('auth::reset_password_successful', TRUE);
            return true;
        }

        $this->setError('auth::reset_password_unsuccessful', TRUE);
        return false;
    }
}