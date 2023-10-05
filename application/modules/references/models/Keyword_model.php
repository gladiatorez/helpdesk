<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Keyword_model extends MY_Model
{
    public $table = 'ref_keywords';
    public $primary_key = 'id';
    public $fillable = [
        'name'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}