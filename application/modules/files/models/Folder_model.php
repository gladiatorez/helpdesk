<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Folder_model extends MY_Model
{
    public $table = 'files_folders';
    public $primary_key = 'id';
    public $fillable = [
        'id','parent_id','slug','name','location','remote_container'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}