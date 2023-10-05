<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Informer_model extends MY_Model
{
    public $table = 'informer';
    public $primary_key = 'id';
    public $fillable = [
        'user_id', 'full_name', 'nik', 'phone', 'company_id', 'company_other', 'department_id', 'department_other',
        'company_branch_id',
        'position', 'photo_file'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function create($data, $userActive = FALSE)
    {
        if (!array_key_exists('full_name', $data) || !array_key_exists('nik', $data) ||
            !array_key_exists('phone', $data) || !array_key_exists('position', $data) ||
            !array_key_exists('email', $data) || !array_key_exists('company_id', $data) || 
            !array_key_exists('password', $data) || !array_key_exists('department_id', $data)) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        $profileData = [
            'full_name' => $data['full_name'],
            'nik'       => $data['nik'],
            'phone'     => $data['phone'],
            'company_id'    => $data['company_id'],
            'company_other' => $data['company_other'],
            'department_id' => $data['department_id'],
            'department_other' => $data['department_other'],
            'position' => $data['position'],
        ];

        // get default user group for staff
        $this->load->model('users/group_model');
        $group = $this->group_model->get(['view_cp' => '0', 'is_default' => '0']);
        if (!$group) {
            return [
                'success' => false,
                'message' => lang('msg::informer::user_group_not_set')
            ];
        }

        $this->_database->trans_start();

        $register = $this->the_auth_frontend->register(
            '',
            $data['password'],
            $data['email'],
            $data['company_id'],
            $group->id,
            $profileData,
            'en',
            $userActive
        );
        if (!$register) {
            $this->_database->trans_rollback();
            return [
                'success' => false,
                'message' => $this->the_auth_frontend->getMessageStr()
            ];
        }

        $this->_database->trans_complete();
        if ($this->_database->trans_status() === false) {
            $this->_database->trans_rollback();
            return [
                'success' => false,
                'message' => lang('msg::saving_failed'),
            ];
        }

        return [
            'success' => true,
            'message' => lang('msg::saving_success'),
            'user_id' => $register
        ];
    }
}