<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Education_model extends MY_Model
{
    public $table = 'education_images';
    public $primary_key = 'id';
    public $fillable = [
        'title', 'file_id', 'is_headline',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}