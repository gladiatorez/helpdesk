<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_note_model extends MY_Model
{
    public $table = 'tickets_note';
    public $primary_key = 'id';
    public $fillable = [
        'ticket_id', 'user_id', 'description', 'created_by', 'updated_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}