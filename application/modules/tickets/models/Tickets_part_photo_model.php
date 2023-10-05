<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_part_photo_model extends MY_Model
{
    public $table = 'tickets_part_photo';
    public $primary_key = 'id';
    public $fillable = [
        'ticket_id', 'file_id'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}