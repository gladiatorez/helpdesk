<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Faq
 *
 * @property Category_model $category_model
 * @property Faq_model $faq_model
 * @property Faq_rate_model $faq_rate_model
 */
class Faq extends Public_Controller
{
    public $_section = 'faq';

    public function __construct()
    {
        parent::__construct();

        $this->load->model('faq/faq_model');
        $this->lang->load('faq/faq');

        // categories
        $this->load->model('references/category_model');
        $categories = $this->category_model->fields(array('id', 'name', 'description', 'icon_class'))
            ->set_cache('all_active_categories')
            ->as_array()
            ->get_all(array('parent_id' => '0', 'active' => 'A'));
        $this->template->set('faq_categories', $categories ? $categories : []);

        ci()->template->append_metadata(
            sprintf('<script>ufhy.faqCategories = %s</script>', $categories ? json_encode($categories) : [])
        );

        $this->template->title('FAQ');
    }

    public function _setVariableCategoryCurrent($id) 
    {
        ci()->template->append_metadata(
            sprintf('<script>ufhy.faqCategorySelected = "%s"</script>', $id ? $id : '')
        );
    }

    public function index()
    {
        $headlineCount = Setting::get('faq_headline_count', 'faq');
        $headline = $this->faq_model->limit($headlineCount)->get_all(array('active' => 'A', 'is_headline' => '1'));
        $this->template->set('headline', $headline);

        $this->template->build('faq/faq-home-f');
    }

    public function category($id = '')
    {
        if (empty($id)) {
            show_404();
        }

        $category = $this->category_model->set_cache('category_' . $id)
            ->get(array('id' => $id, 'active' => 'A'));
        if ($category === false) {
            $this->template->show_404('CATEGORY_NOT_FOUND');
            return false;
        }
        $this->_setVariableCategoryCurrent($category['id']);
        $this->template->set('category', $category);

        $limitPerPage = Setting::get('faq_row_per_page', 'faq');
        $startIndex = ($this->input->get('page')) ? $this->input->get('page') : 0;
        $ipAddress = $this->input->ip_address();

        $this->load->library('pagination');
        $config['base_url'] = site_url('faq/category/' . $id);
        $config['total_rows'] = $this->faq_model->count_rows(array('category_id' => $category['id'], 'active' => 'A'));
        $config['per_page'] = $limitPerPage;
        $config['num_links'] = 5;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $this->pagination->initialize($config);
        $this->template->set('page_links', $this->pagination->create_links_vuetify());

        $this->load->model('faq/faq_rate_model');
        $faq = $this->faq_model->limit($limitPerPage, $startIndex)
            ->order_by('id', 'desc')
            ->get_all(array('category_id' => $category['id'], 'active' => 'A'));
        if ($faq) {
            foreach ($faq as $item) {
                $item->is_rate = false;
                $countRate = $this->faq_rate_model->count_rows(['faq_id' => $item->id, 'ip_address' => $ipAddress]);
                if ($countRate > 0) {
                    $item->is_rate = true;
                }
            }
        }
        $this->template->set('faq_list', $faq);

        $this->template
            ->pageTitle($category['name'])
            ->append_js('webpack::dist/page.faq.js', true, 'page')
            ->build('faq/faq-category-f');
    }

    public function item($slug = '')
    {
        if (empty($slug)) {
            show_404();
        }

        $faq = $this->faq_model->get(array('slug' => $slug));
        if ($faq === false) {
            $this->template->show_404('FAQ_ITEM_NOT_FOUND');
            return false;
        }

        // get category
        $category = $this->category_model->set_cache('category_' . $faq->category_id)
            ->get(['id' => $faq->category_id]);
        $this->template->set('category', $category ? $category : []);

        // ambil data rating berdasarkan ip address
        $this->load->model('faq/faq_rate_model');
        $rate = $this->faq_rate_model->count_rows(array('faq_id' => $faq->id, 'ip_address' => $this->input->ip_address()));
        $faq->is_rate = $rate >= 1 ? true : false;

        $this->_setVariableCategoryCurrent($faq->category_id);
        $this->template->set('faq', $faq);
        
        $this->template
            ->pageTitle(sprintf('%s : %s', $category['name'], $faq->title))
            ->build('faq/faq-item-f');
    }

    public function rate()
    {
        if ($this->input->is_ajax_post()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('id', 'ID', 'trim|required');
            $this->form_validation->set_rules('option', 'Pilihan', 'trim|required');
            if ($this->form_validation->run() === false) {
                $result = array(
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                );
            } else {
                $this->load->model('faq/faq_rate_model');

                $id = $this->input->post('id');
                $option = $this->input->post('option');
                $ipAddress = $this->input->ip_address();
                $exec = $this->faq_rate_model->insert_rate($id, $option, $ipAddress);
                if ($exec) {
                    Events::trigger('faq_rated', $id);
                    $result = array(
                        'success' => true,
                        'message' => ''
                    );
                } else {
                    $result = array(
                        'success' => false,
                        'message' => lang('msg::saving_failed')
                    );
                }
            }

            $this->template->set_layout(false)
                ->build_json($result);
        }
    }

    public function search()
    {
        if ($this->input->post('q')) {
            redirect(current_url() . '?q=' . $this->input->post('q'));
        }

        if ($this->input->get('q') == null) {
            show_404();
            return false;
        }

        $query = $this->input->get('q');
        $filter = array(
            'faq' => 'faq:faq'
        );

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

        $this->template
            ->pageTitle(sprintf(lang('heading_search_result'), $query))
            ->set('query', $query)
            ->build('faq/frontend/search_result');
    }
}