<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_view_model extends MY_Model
{
    public $table = 'tickets_view';
    public $primary_key = 'id';
    public $fillable = false;

    public function __construct()
    {
        $this->soft_deletes = FALSE;
        
        $this->has_many['staffs'] = [
            'tickets/Tickets_staff_view_model', 'ticket_id', 'id'
        ];
        $this->has_many['logs'] = [
            'tickets/Tickets_log_model', 'ticket_id', 'id'
        ];

        $this->has_many['notes'] = [
            'tickets/Tickets_note_view_model', 'ticket_id', 'id'
        ];
        $this->has_many['attachment'] = [
            'tickets/Tickets_file_model', 'ticket_id', 'id'
        ];
        $this->has_many['part_list'] = [
            'tickets/Tickets_part_list_model', 'ticket_id', 'id'
        ];
        $this->has_many['part_photos'] = [
            'tickets/Tickets_part_photo_model', 'ticket_id', 'id'
        ];
        $this->has_many['work_result'] = [
            'tickets/Tickets_work_result', 'ticket_id', 'id'
        ];

        parent::__construct();
    }
}