<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Group_model extends MY_Model
{
    public $table = 'user_groups';
    public $primary_key = 'id';
    public $fillable = [
        'name','description','is_default','is_admin', 'view_cp', 'send_ticket','is_helpdesk'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function getDefaultGroup()
    {
        $group = $this->fields('id, name')
            ->set_cache('user_group_default')
            ->get(['is_default' => true]);
        return $group;
    }

    public function getInformerGroup()
    {
        $group = $this->fields('id, name')
            // ->set_cache('user_group_informer')
            ->get_all(['send_ticket' => '1']);
        // die(json_encode($group));
        if (!$group) {
            return false;
        }
        $result = [];
        foreach ($group as $row) {
            array_push($result, $row->id);
        }
        return $result;
    }

    public function canSendTicket()
    {
        $group = $this->fields('id, name')
            // ->set_cache('user_group_informer')
            ->get_all(['send_ticket' => '1']);
        // die(json_encode($group));
        if (!$group) {
            return false;
        }
		$result = [];
        foreach ($group as $row) {
            array_push($result, $row->id);
        }
        
        return $result;
    }

    public function create($data)
    {
        if ( !array_key_exists('name', $data) ) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('name', $data['name'])) {
            return false;
        }

        if ($data['is_default']) {
            $this->update(['is_default' => 0], ['is_default' => 1]);
        }

        return $this->insert($data);
    }

    public function edit($id, $data)
    {
        if ( !array_key_exists('name', $data) ) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('name', $data['name'], $id)) {
            return false;
        }

        if ($data['is_default']) {
            $this->update(['is_default' => 0], ['is_default' => 1]);
        }

        return $this->update($data, ['id' => $id]);
    }
}