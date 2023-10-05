<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Tickets_part_list_model
 * @property Part_list_model $part_list_model
 */
class Tickets_part_list_model extends MY_Model
{
    public $table = 'tickets_part_list';
    public $primary_key = 'id';
    public $fillable = [
        'ticket_id', 'user_id', 'name', 'qty', 'created_at', 'updated_at'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function create($ticketId, $partName, $qty = 1)
    {
        $count = $this->count_rows(['ticket_id' => $ticketId, 'name' => $partName]);
        if ($count >= 1) {
            return FALSE;
        }

        $this->load->model('references/part_list_model');
        $part = $this->part_list_model->get(['name' => $partName]);
        if (!$part) {
            $this->part_list_model->insert([
                'name' => $partName,
            ]);
        }

        return $this->insert([
            'ticket_id' => $ticketId,
            'user_id' => ci()->currentUser->id,
            'name' => $partName,
            'qty' => $qty
        ]);
    }
}