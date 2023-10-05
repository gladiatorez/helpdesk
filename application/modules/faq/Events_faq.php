<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Events_faq
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();

        $this->ci->load->model('search/search_index_model');

        Events::register('faq::created', array($this, 'index_faq'));
        Events::register('faq::edited', array($this, 'index_faq'));
        Events::register('faq::removed', array($this, 'drop_faq'));
    }

    public function index_faq($id)
    {
        $this->ci->load->model('faq/faq_model');
        $faq = $this->ci->faq_model->get(array('id' => $id));
        if ($faq === FALSE) {
            $this->ci->search_index_model->drop_index('faq', 'faq:faq', $id);
        }
        else {
            $this->ci->search_index_model->index(
                'faq',
                'faq:faq',
                'faq:faq',
                $id,
                'faq/item/'.$faq->slug,
                $faq->title,
                $faq->description,
                array(
                    'cp_edit_uri' => BACKEND_URLPREFIX.'/faq/edit/'.$id,
                    'keywords' => $faq->keywords
                )
            );
        }
    }

    public function remove_faq($id)
    {
        $this->ci->search_index_model->drop_index('faq', 'faq:faq', $id);
    }

}