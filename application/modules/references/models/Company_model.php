<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends MY_Model
{
    public $table = 'ref_companies';
    public $primary_key = 'id';
    public $fillable = [
        'name', 'abbr', 'address', 'telephone', 'faximile', 'email', 'website', 'active',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = TRUE;
        $this->has_many['branch'] = [
            'references/company_branch_model', 'company_id', 'id'
        ];

        parent::__construct();
    }

    public function create($data)
    {
        if (!array_key_exists('name', $data) || !array_key_exists('abbr', $data)) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('name', $data['name'])) {
            return false;
        }

        $this->set_before_create('add_creator');
        $this->set_before_create('add_updater');

        return $this->insert($data);
    }

    public function edit($id, $data)
    {
        if (!array_key_exists('name', $data) || !array_key_exists('abbr', $data)) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('name', $data['name'], $id)) {
            return false;
        }

        $this->set_before_update('add_updater');
        return $this->update($data, ['id' => $id]);
    }
}