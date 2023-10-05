<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Keyword_applied_model extends MY_Model
{
    public $table = 'ref_keywords_applied';
    public $primary_key = 'id';
    public $fillable = [
        'hash', 'keyword_id'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        $this->has_one['detail'] = [
            'references/Keyword_model', 'id', 'keyword_id'
        ];

        parent::__construct();
    }
}