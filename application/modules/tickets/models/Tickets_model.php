<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_model extends MY_Model
{
    public $table = 'tickets';
    public $primary_key = 'id';
    public $fillable = [
        'uid', 'urt', 'number', 'subject', 'description', 'informer_id', 'company_id', 'department_id', 'department_other',
        'services_id', 'category_id', 'category_sub_id', 'priority_id', 'flag', 'is_read', 'keywords', 'pic_level', 'estimate', 
        'skala_position', 'skala_job', 'skala_availability', 'skala_operation', 'estimate_response_time',
        'company_branch_id',
        'cause_descr', 'solution_descr',
        'duration','duration_hold','date_opened','date_closed',
        'duration_work','response_helpdesk','response_pic','leadtime',
        'reason_cancel','network'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;
        
        $this->has_many['staff'] = [
            'tickets/Tickets_staff_model', 'ticket_id', 'id'
        ];
        $this->has_many['staff_v'] = [
            'tickets/Tickets_staff_view_model', 'ticket_id', 'id'
        ];
        $this->has_many['logs'] = [
            'tickets/Tickets_log_model', 'ticket_id', 'id'
        ];
        $this->has_many['notes'] = [
            'tickets/Tickets_note_view_model', 'ticket_id', 'id'
        ];
        $this->has_one['informer'] = [
            'informer/Informer_model', 'id', 'informer_id'
        ];
        $this->has_many['notes'] = [
            'tickets/Tickets_note_view_model', 'ticket_id', 'id'
        ];
        $this->has_many['part_list'] = [
            'tickets/Tickets_part_list_model', 'ticket_id', 'id'
        ];

        parent::__construct();
    }

    public function returnIt($ticketId, $reason = '')
    {
        if (empty($reason)) {
            return false;
        }

        $this->load->model('tickets/tickets_log_model');
        $this->load->model('tickets/tickets_staff_model');
        $this->load->model('tickets/tickets_note_model');

        $this->_database->trans_start();

        $this->update(
            ['flag' => TICKET_FLAG_REQUESTED, 'is_read' => '0'],
            ['id' => $ticketId]
        );

        $this->tickets_log_model->where(['ticket_id' => $ticketId])
            ->where('event', ['TICKET_REQUEST'], NULL, FALSE, TRUE)
            ->delete();
        $this->tickets_staff_model->delete(['ticket_id' => $ticketId]);

        $this->tickets_note_model->delete(['ticket_id' => $ticketId]);
        $this->tickets_note_model->insert([
            'ticket_id' => $ticketId,
            'user_id' => $this->the_auth_backend->getUserLoginId(),
            'description' => $reason
        ]);
        
        $this->_database->trans_complete();
        if ($this->_database->trans_status() === FALSe) {
            $this->_database->trans_rollback();
            return false;
        }

        return true;
    }
}