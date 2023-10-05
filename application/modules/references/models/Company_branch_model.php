<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Company_branch_model extends MY_Model
{
    public $table = 'ref_companies_branch';
    public $primary_key = 'id';
    public $fillable = [
        'company_id', 'name','active',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = TRUE;

        parent::__construct();
    }

    public function create($data)
    {
        if (!array_key_exists('name', $data) || !array_key_exists('company_id', $data)) {
            return false;
        }

        $find = $this->count_rows([
            'name' => $data['name'],
            'company_id' => $data['company_id'],
        ]);
        if ($find > 0) {
            return false;
        }

        $this->set_before_create('add_creator');
        $this->set_before_create('add_updater');

        return $this->insert($data);
    }

    public function edit($id, $data)
    {
        if (!array_key_exists('name', $data) || !array_key_exists('company_id', $data)) {
            return false;
        }

        $find = $this->count_rows([
            'name' => $data['name'],
            'company_id' => $data['company_id'],
            'id !='  => $id,
        ]);
        if ($find > 0) {
            return false;
        }

        $this->set_before_update('add_updater');
        return $this->update([
            'name' => $data['name'],
            'active' => $data['active']
        ], ['id' => $id]);
    }
}