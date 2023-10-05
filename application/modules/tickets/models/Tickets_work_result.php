<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_work_result extends MY_Model
{
    public $table = 'tickets_work_result';
    public $primary_key = 'id';
    public $fillable = [
        'ticket_id', 'file_id'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        $this->has_one['file'] = [
            'files/File_model', 'id', 'file_id'
        ];

        parent::__construct();
    }
}