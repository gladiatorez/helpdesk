<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reason_model extends MY_Model
{
    public $table = 'ref_reasons';
    public $primary_key = 'id';
    public $fillable = [
        'description', 'active', 'created_by', 'updated_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = TRUE;

        parent::__construct();
    }
}