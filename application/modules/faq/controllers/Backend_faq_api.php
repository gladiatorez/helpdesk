<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_faq_api
 *
 * @property Faq_model $faq_model
 * @property Faq_rate_model $faq_rate_model
 */
class Backend_faq_api extends Backend_Api_Controller
{
    public $_section = 'read';

    protected $_validationRules = [
        ['field' => 'title', 'label' => 'lang:faq::title', 'rules' => 'trim|required|max_length[255]|callback__check_title'],
        // ['field' => 'slug', 'label' => 'lang:faq::slug', 'rules' => 'trim|required|max_length[255]|callback__check_slug'],
        ['field' => 'descr', 'label' => 'lang:faq::descr', 'rules' => 'trim|required'],
        ['field' => 'categoryId', 'label' => 'lang:faq::category', 'rules' => 'trim|required'],
        ['field' => 'active', 'label' => 'lang:lb::active', 'rules' => 'trim|required'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('faq/faq_model');
        $this->lang->load('faq/faq');
    }

    public function _check_title($title = '')
    {
        if ($this->faq_model->with_trashed()->check_unique_field('title', $title, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_title', sprintf(lang('msg::faq::title_already_exist'), $title));
            return false;
        }

        return true;
    }

    public function _check_slug($slug = '')
    {
        if ($this->faq_model->with_trashed()->check_unique_field('slug', $slug, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_slug', sprintf(lang('msg::faq::slug_already_exist'), $slug));
            return false;
        }

        return true;
    }

    public function index()
    {
        $this->load->library('bt_server');
        $this->load->model('faq/faq_rate_model');

        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'title', 'bt' => 'title'],
            ['db' => 'description', 'bt' => 'descr'],
            ['db' => 'slug', 'bt' => 'slug'],
            ['db' => 'keywords', 'bt' => 'keywords'],
            ['db' => 'is_headline', 'bt' => 'isHeadline', 'formatter' => function($val) {
                return $val > 0;
            }],
            array('db' => 'id', 'bt' => 'rated', 'formatter' => function ($val) {
                $rating = $this->faq_rate_model->fields('faq_id,`options`,COUNT(*) as count', false)
                    ->group_by('`options`')
                    ->get_all(array('faq_id' => $val));
                $resultRate = array('Y' => 0, 'N' => 0);

                if ($rating !== false) {
                    foreach ($rating as $rate) {
                        if ($rate->options === 'Y') {
                            $resultRate['Y'] = $rate->count;
                        }
                        if ($rate->options === 'N') {
                            $resultRate['N'] = $rate->count;
                        }
                    }
                }

                return $resultRate;

            }),
            ['db' => 'active', 'bt' => 'active'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['title'])
            ->process($request, $columns, $this->faq_model->table);
        $this->template->build_json($results);
    }

    public function item()
    {
        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $item = $this->faq_model->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $this->load->library('references/keyword');

        $data = [
            'id'            => $item->id,
            'title'         => $item->title,
            'categoryId'    => $item->category_id,
            'descr'         => $item->description,
            'slug'          => $item->slug,
            'keywords'      => Keyword::getArray($item->keywords),
            'isHeadline'    => $item->is_headline > 0,
            'active'        => $item->active,
            'createdAt'     => $item->created_at,
            'updatedAt'     => $item->updated_at,
        ];

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
    }

    public function form_options()
    {
        // get parent category
        $this->load->model('references/category_model');
        $categories = $this->category_model->fields('id, name')
            ->order_by('name')
            ->as_array()
            ->get_all(['parent_id' => 0, 'active' => 'A']);

        $this->template->build_json([
            'categoryOptions' => $categories ? $categories : [],
        ]);
    }

    public function keywords()
    {
        if (!$this->input->get('q')) {
            $this->template->build_json([]);
            return false;
        }

        $q = $this->input->get('q');
        
        $this->load->model('references/keyword_model');
        $keywords = $this->keyword_model->fields('id,name')
            ->as_array()
            ->where('name', 'like', $q)
            ->get_all();
        if (!$keywords) {
            $this->template->build_json([]);
            return false;
        }

        $this->template->build_json($keywords);
    }

    public function create()
    {

        userHasRoleOrDie('create', 'faq');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run())
        {
            $this->load->library('references/keyword');

            $slug = $this->input->post('slug');
            if (!$this->input->post('slug')) {
                $slug = slugify($this->input->post('title', TRUE));
            }
            
            $data = [
                'category_id'   => $this->input->post('categoryId', TRUE),
                'title'         => $this->input->post('title', TRUE),
                'description'   => $this->input->post('descr'),
                'slug'          => $slug,
                'is_headline'   => $this->input->post('isHeadline'),
                'active'        => $this->input->post('active'),
                'keywords'      => Keyword::process($this->input->post('keywords'))
            ];

            $result = $this->faq_model->create($data);
            if ($result) {
                Events::trigger('faq::created', $result);
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        }
        else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function edit()
    {
        userHasRoleOrDie('edit', 'faq');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:lb::id', 'trim|required');
        if ($this->form_validation->run())
        {
            // get faq 
            $faq = $this->faq_model->get(['id' => $this->input->post('id', TRUE)]);
            if (!$faq) {
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);

                return false;
            }

            $this->load->library('references/keyword');
            $oldKeywordsHash = (trim($faq->keywords) != '') ? $faq->keywords : null;
            $slug = $this->input->post('slug');
            if (!$this->input->post('slug')) {
                $slug = slugify($this->input->post('title', true));
            }

            $data = [
                'category_id'   => $this->input->post('categoryId', TRUE),
                'title'         => $this->input->post('title', TRUE),
                'description'   => $this->input->post('descr'),
                'slug'          => $slug,
                'is_headline'   => $this->input->post('isHeadline'),
                'active'        => $this->input->post('active'),
                'keywords'      => Keyword::process($this->input->post('keywords'), $oldKeywordsHash)
            ];

            $result = $this->faq_model->edit($this->input->post('id', TRUE), $data);
            if ($result) {
                Events::trigger('faq::edited', $this->input->post('id', TRUE));
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        }
        else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function remove()
    {
        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $department = $this->faq_model->fields('id,title')->get(['id' => $id]);    // find partner
        if (!$department) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->faq_model->set_before_soft_delete('add_deleted');
        $remove = $this->faq_model
            ->delete(['id' => $this->input->get('id', TRUE)]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        Events::trigger('faq::removed', $this->input->get('id', true));
        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $department->title)
        ]);

        return true;
    }
}