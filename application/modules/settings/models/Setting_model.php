<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends MY_Model
{
    public $table = 'settings';

    public $primary_key = 'slug';

    public function __construct()
    {
        parent::__construct();
    }
}