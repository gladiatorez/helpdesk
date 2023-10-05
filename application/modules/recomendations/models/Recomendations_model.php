<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Tickets_view_model $tickets_view_model
 */
class Recomendations_model extends MY_Model
{
    public $table = 'recomendations';
    public $primary_key = 'id';
    public $fillable = [
        'letter_no','letter_no_suffix','letter_subject','letter_date','letter_attach', 
        'ticket_id', 'ticket_number', 'ticket_subject', 'ticket_informer_full_name', 'ticket_company_name',
        'ticket_department_name', 'ticket_company_branch_name',
        'descr_background','descr_examination','descr_handling','descr_results','descr_recomendation',
        'maker_full_name', 'maker_position','approve_full_name','approve_position','approve_user_id',
        'created_by', 'updated_by', 'deleted_by', 'serial_number'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        $this->has_many['photos'] = [
            'recomendations/Recomendations_photo_model', 'recomendation_id', 'id'
        ];

        parent::__construct();
    }

    public function create($data)
    {
        if (!array_key_exists('letter_no', $data) || !array_key_exists('letter_no_suffix', $data) || 
            !array_key_exists('letter_subject', $data) || !array_key_exists('letter_date', $data) || 
            !array_key_exists('ticket_id', $data) || 
            !array_key_exists('descr_background', $data) || !array_key_exists('descr_recomendation', $data)) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        $find = (bool) $this->recomendations_model
            ->count_rows([
                'letter_no' => $data['letter_no'],
                'letter_no_suffix' => $data['letter_no_suffix']
            ]);
        if ($find) {
            return [
                'success' => false,
                'message' => sprintf(lang('msg::recomendations::letter_no_already_exist'), $data['letter_no'])
            ];
        }

        $this->load->model('tickets/tickets_view_model');
        $ticket = $this->tickets_view_model->get(['id' => $data['ticket_id']]);
        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Ticket not found'
            ];
        }
        if ($ticket->flag === TICKET_FLAG_REQUESTED) {
            return [
                'success' => false,
                'message' => 'Ticket is still in the requested flag'
            ];
        }

        $data['ticket_number'] = $ticket->number;
        $data['ticket_subject'] = $ticket->subject;
        $data['ticket_informer_full_name'] = $ticket->informer_full_name;
        $data['ticket_company_name'] = $ticket->company_name ? $ticket->company_name : '';
        $data['ticket_department_name'] = $ticket->department_name ? $ticket->department_name : '';
        $data['ticket_company_branch_name'] = $ticket->company_branch_name ? $ticket->company_branch_name : '';

        $this->load->model('staff/staff_view_model');
        $staffMaker = $this->staff_view_model
            ->fields('id,user_id,full_name,position,parent_id')
            ->get(['user_id' => ci()->currentUser->id]);
        if (!$staffMaker) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }
        $data['maker_full_name'] = $staffMaker->full_name;
        $data['maker_position'] = $staffMaker->position;

        $staffApprovedBy = $this->staff_view_model
            ->fields('id,user_id,full_name,position')
            ->get(['id' => $staffMaker->parent_id]);
        if ($staffApprovedBy) {
            $data['approve_full_name'] = $staffApprovedBy->full_name;
            $data['approve_position'] = $staffApprovedBy->position;
            $data['approve_user_id'] = $staffApprovedBy->user_id;
        }

        $this->set_before_create('add_creator');
        $this->set_before_create('add_updater');

        $insert = $this->insert($data);

        return [
            'success' => true,
            'message' => lang('msg::saving_success'),
            'id' => $insert
        ];
    }

    public function edit($id, $data)
    {
        if (!array_key_exists('letter_no', $data) || !array_key_exists('letter_no_suffix', $data) || 
            !array_key_exists('letter_subject', $data) || !array_key_exists('letter_date', $data) || 
            !array_key_exists('ticket_id', $data) || 
            !array_key_exists('descr_background', $data) || !array_key_exists('descr_recomendation', $data)) {
            return [
                'success' => false,
                'message' => lang('msg::saving_failed')
            ];
        }

        $find = (bool) $this->recomendations_model
            ->where('id !=', $this->input->post('id', TRUE))
            ->count_rows([
                'letter_no' => $data['letter_no'],
                'letter_no_suffix' => $data['letter_no_suffix']
            ]);
        if ($find) {
            return [
                'success' => false,
                'message' => sprintf(lang('msg::recomendations::letter_no_already_exist'), $data['letter_no'])
            ];
        }

        $this->load->model('tickets/tickets_view_model');
        $ticket = $this->tickets_view_model->get(['id' => $data['ticket_id']]);
        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Ticket not found'
            ];
        }
        if ($ticket->flag === TICKET_FLAG_REQUESTED) {
            return [
                'success' => false,
                'message' => 'Ticket is still in the requested flag'
            ];
        }

        unset($data['letter_no']);
        unset($data['letter_no_suffix']);
        unset($data['letter_date']);
        $data['ticket_number'] = $ticket->number;
        $data['ticket_subject'] = $ticket->subject;
        $data['ticket_informer_full_name'] = $ticket->informer_full_name;
        $data['ticket_company_name'] = $ticket->company_name ? $ticket->company_name : '';
        $data['ticket_department_name'] = $ticket->department_name ? $ticket->department_name : '';
        $data['ticket_company_branch_name'] = $ticket->company_branch_name ? $ticket->company_branch_name : '';
        
        $this->set_before_update('add_updater');
        $this->update($data, ['id' => $id]);

        return [
            'success' => true,
            'message' => lang('msg::saving_success'),
            'id' => $id
        ];
    }
}