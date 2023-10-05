<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Faq_rate_model extends MY_Model
{
    public $table = 'faq_rate';
    public $primary_key = 'id';
    public $fillable = [
        'faq_id', 'options', 'ip_address', 'guest', 'created_at'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function insert_rate($faqId, $option, $ipAddress)
    {
        $get = $this->get(array('faq_id' => $faqId, 'ip_address' => $ipAddress));
        if ($get) {
            return false;
        }

        $insert = $this->insert(array(
            'faq_id' => $faqId,
            'options' => strtoupper($option),
            'ip_address' => $ipAddress,
            'created_at' => date($this->timestamps_format),
            'guest' => $this->input->user_agent()
        ));
        if ($insert === false) {
            return false;
        }

        return $insert;
    }
}