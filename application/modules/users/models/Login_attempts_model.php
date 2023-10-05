<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Login_attempts_model extends MY_Model
{
    public $table = 'user_login_attempts';
    public $primary_key = 'id';
    public $fillable = [
        'ip_address','login','time'
    ];
    public $timestamps = FALSE;

    public function __construct()
    {
        parent::__construct();
    }

    public function getAttemptsNum($identity, $time = 600)
    {
        $ipAddress = $this->input->ip_address();
        $this->_database->where(array('ip_address' => $ipAddress, 'login' => $identity));
        $this->_database->where('time >', time() - $time, FALSE);
        $qres = $this->_database->get($this->table);

        return $qres->num_rows();
    }

    public function clearLoginAttempts($identity, $oldAttemptsExpirePeriod = 86400, $time = 600)
    {
        $oldAttemptsExpirePeriod = max($oldAttemptsExpirePeriod, $time);

        $this->_database->where('login', $identity);
        $this->_database->or_where('time <', time() - $oldAttemptsExpirePeriod, FALSE);
        return $this->_database->delete($this->table);
    }
}