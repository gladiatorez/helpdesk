<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Search
 *
 * @property Category_model $category_model
 * @property Faq_model $faq_model
 * @property Faq_rate_model $faq_rate_model
 */
class Search extends Public_Controller
{
    public $_section = 'search';

    public function __construct()
    {
        parent::__construct();
        
        $this->lang->load('search/search');
        $this->template->title('Search');
    }

    public function index()
    {
        if ($this->input->post('q')) {
            redirect(current_url() . '?q=' . $this->input->post('q'));
        }

        if ($this->input->get('q') == null) {
            show_404();
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
        $total = $this->search_index_model
            ->filter($filter)
            ->count($query);
        $this->template->set('total_result', $total);

        $limitPerPage = Setting::get('faq_row_per_page', 'faq');
        $startIndex = ($this->input->get('page')) ? $this->input->get('page') : 0;
        
        $this->load->library('pagination');
        $config['base_url'] = site_url('faq/search?q=' . $query);
        $config['total_rows'] = $total;
        $config['per_page'] = $limitPerPage;
        $config['num_links'] = 5;
        $config['page_query_string'] = true;
        $config['query_string_segment'] = 'page';
        $this->pagination->initialize($config);
        $this->template->set('page_links', $this->pagination->create_links_vuetify());

        $results = $this->search_index_model
            ->limit($limitPerPage, $startIndex)
            ->filter($filter)
            ->search($query);
        $this->template->set('results', $results);

        ci()->template->append_metadata(
            sprintf('<script>ufhy.searchQuery = "%s"</script>', $query ? $query : '')
        );

        $this->template
            ->pageTitle(sprintf('result : %s', $query))
            ->append_js('webpack::dist/page.search-result.js', true, 'page')
            ->set('query', $query)
            ->build('search/search-result-f');
    }
}