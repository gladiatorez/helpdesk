<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Search_api
 *
 */
class Search_api extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(false);
    }

    public function index()
    {
        if ($this->input->get('q') == null) {
            $this->template->build_json([]);
            return false;
        }

        $query = $this->input->get('q');
        $filter = [];
        if (!frontendIsLoggedIn()) {
            $filter = array(
                'faq' => 'faq:faq'
            );
        }

        $this->load->model('search/search_index_model');
        $results = $this->search_index_model
            ->limit(10)
            ->filter($filter)
            ->search($query);
        $this->template->build_json($results);
    }
}