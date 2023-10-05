<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_note_view_model extends MY_Model
{
    public $table = 'tickets_note_view';
    public $primary_key = 'id';
    public $fillable = false;

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}