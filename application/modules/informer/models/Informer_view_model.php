<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Informer_view_model extends MY_Model
{
    public $table = 'informer_view';
    public $primary_key = 'id';
    public $fillable = [];

    public function __construct()
    {
        parent::__construct();
    }
}