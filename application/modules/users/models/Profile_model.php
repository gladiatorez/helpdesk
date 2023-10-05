<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Profile_model extends MY_Model
{
    public $table = 'user_profile';
    public $primary_key = 'user_id';
    public $fillable = [
        'user_id','full_name','phone','photo_file','nik','position'
    ];

    public function __construct()
    {
        $this->timestamps = FALSE;
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function updateCompanyInfo($userId, $companyId, $nik, $position)
    {
        if (empty($userId)) {
            return false;
        }

        $this->load->model('references/company_model');
        $findCompany = $this->company_model->count_rows(['id' => $companyId]);
        if ($findCompany <= 0) {
            return false;
        }

        $this->_database->trans_start();

        // update profile table
        $this->update([
            'nik' => $nik,
            'position' => $position
        ], ['user_id' => $userId]);

        // update user table
        $this->user_model->update([
            'company_id' => $companyId
        ], ['id' => $userId]);

        $this->_database->trans_complete();
        if ($this->_database->trans_status() === FALSE) {
            $this->_database->trans_rollback();
            return false;
        }

        return true;
    }
}