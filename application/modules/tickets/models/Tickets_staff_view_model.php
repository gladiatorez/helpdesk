<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_staff_view_model extends MY_Model
{
    public $table = 'tickets_staff_view';
    public $primary_key = 'id';
    public $fillable = FALSE;

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function queryTicketByUserId($number, $userId = null)
    {
        if (!$userId) {
            $userId = $this->the_auth_backend->getUserLoginId();
        }

        $query = $this->_database->select([
                'ticket.id','ticket.number','ticket.informer_full_name','ticket.company_name',
                'ticket.company_branch_name','ticket.department_name',
                'ticket.subject'
            ])
            ->from($this->table . ' AS ticketStaff')
            ->join('tickets_view AS ticket', 'ticketStaff.ticket_id = ticket.id')
            ->group_start()
            ->where_in('ticketStaff.user_id', $userId)
            ->where_in('ticketStaff.is_claimed', '1')
            ->group_end()
            ->group_start()
            ->like('ticket.number', $number)
            ->group_end()
            ->order_by('ticket.created_at', 'DESC')
            ->limit(10)
            ->get();
            
        
        return $query->result();
    }
}