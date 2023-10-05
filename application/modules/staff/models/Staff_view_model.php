<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Staff_view_model
 *
 */
class Staff_view_model extends MY_Model
{
    public $table = 'staff_view';

    public $primary_key = 'id';

    public $fillable = FALSE;
    public $timestamps = FALSE;

    public function staffHasTickets()
    {
        $this->_database->select([
            'staff.id','staff.full_name AS fullName','staff.position',
            '(
                SELECT COUNT(tstaff.id) FROM uf_tickets_staff_view tstaff WHERE tstaff.staff_id = staff.id AND tstaff.ticket_flag = "PROGRESS"
            ) AS `hasTicket`',
            '(
                SELECT COUNT(tstaff1.id) FROM uf_tickets_staff_view tstaff1 WHERE tstaff1.staff_id = staff.id AND tstaff1.ticket_flag = "HOLD"
            ) AS `holdCount`'
        ]);
        $this->_database->from('staff_view staff');
        $this->_database->where('staff.active', 1);
        $this->_database->order_by('staff.full_name', 'ASC');
        $query = $this->_database->get();

        $results = $query->result();
        foreach ($results as &$result) {
            $result->progressCount = (int) $result->hasTicket;
            $result->hasTicket = (bool) $result->hasTicket;
        }
        
        return $results;
    }
}