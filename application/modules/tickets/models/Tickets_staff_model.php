<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_staff_model extends MY_Model
{
    public $table = 'tickets_staff';
    public $primary_key = 'id';
    public $fillable = [
        'ticket_id', 'staff_id', 'is_read', 'created_by', 'updated_by', 'is_claimed', 'response_time'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}