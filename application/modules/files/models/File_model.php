<?php defined('BASEPATH') OR exit('No direct script access allowed');

class File_model extends MY_Model
{
    public $table = 'files';
    public $primary_key = 'id';
    public $fillable = [
        'id','folder_id','user_id','type','name','filename','path','description','extension',
        'mimetype','keywords','width','height','filesize','alt_attribute','download_count'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function exists($fileId)
    {
        return (bool) ($this->count_rows(['id' => $fileId]) > 0);
    }
}