<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_log_model extends MY_Model
{
    public $table = 'tickets_log';
    public $primary_key = 'id';
    public $fillable = [
        'ticket_id', 'event', 'event_by', 'event_by_ref_table', 'event_to', 'event_to_ref_table', 'event_from_id',
        'reason', 'event_date'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function getUserEvent($id, $ref)
    {
        $this->load->model($ref);
        $model = explode('/', $ref);
        if ($model[1] == 'user_model') {
            $get = $this->{$model[1]}->fields(array('username', 'email'))
                ->with('profile', array('fields:full_name,nik'))
                // ->with('staff', array('fields:nik'))
                ->get(array('id' => $id));
            if ($get) {
                return array(
                    'id' => $get->id,
                    'nik' => $get->profile ? $get->profile->nik : '',
                    'fullName' => $get->profile ? $get->profile->full_name : '',
                );
            } else {
                return null;
            }
        } else if ($model[1] == 'informer_model') {
            $get = $this->{$model[1]}->fields(array('id','full_name','nik'))->get(array('id' => $id));
            if ($get) {
                return array(
                    'id' => $get->id,
                    'nik' => $get->nik,
                    'fullName' => $get->full_name,
                );
            } else {
                return null;
            }
        }

        return null;
    }
}