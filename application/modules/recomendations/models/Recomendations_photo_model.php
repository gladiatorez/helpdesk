<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Tickets_view_model $tickets_view_model
 */
class Recomendations_photo_model extends MY_Model
{
    public $table = 'recomendations_photos';
    public $primary_key = 'id';
    public $fillable = [
        'recomendation_id','file','description',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }
}