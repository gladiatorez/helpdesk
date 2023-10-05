<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Email_list_model extends MY_Model
{
    public $table = 'ref_email_list';
    public $primary_key = 'id';
    public $fillable = [
        'email', 'active', 'created_by', 'updated_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}