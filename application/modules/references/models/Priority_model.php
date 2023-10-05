<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Priority_model extends MY_Model
{
    public $table = 'ref_priority';
    public $primary_key = 'id';
    public $fillable = [
        'name', 'ord', 'created_by', 'updated_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}