<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Category_staff_model extends MY_Model
{
    public $table = 'ref_categories_staff';
    public $primary_key = 'id';
    public $fillable = [
        'category_id', 'user_id',
        'created_by', 'updated_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;
        $this->has_one['staff'] = [
            'staff/Staff_model', 'user_id', 'user_id'
        ];

        parent::__construct();
    }
}