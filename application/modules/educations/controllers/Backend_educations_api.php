<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_faq_api
 *
 * @property Education_model $education_model
 */
class Backend_educations_api extends Backend_Api_Controller
{
    public $_section = 'read';

    public function __construct()
    {
        parent::__construct();

        $this->load->model('educations/education_model');
        $this->lang->load('educations/educations');
    }

    public function index()
    {
        $this->load->library('bt_server');

        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'title', 'bt' => 'title'],
            ['db' => 'file_id', 'bt' => 'file'],
            ['db' => 'is_headline', 'bt' => 'isHeadline', 'formatter' => function($val) {
                return $val > 0;
            }],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['title'])
            ->process($request, $columns, $this->education_model->table);
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

    public function create()
    {
        userHasRoleOrDie('create', 'educations');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('title','Title', 'trim|required|max_length[255]');
        if ($this->form_validation->run())
        {
            $this->load->library('files/the_file');

            $title = $this->input->post('title', TRUE);
            $upload = The_file::upload(50, false, 'image',false, false, false, 'jpg|jpeg|png', $title, false, $title);
            if (!$upload['status']) {
                $this->output->set_status_header('400', $upload['message']);
                $this->template->build_json([
                    'success' => false,
                    'message' => $upload['message']
                ]);
            } else {
                $insert = $this->education_model->insert([
                    'title' => $title,
                    'file_id' => $upload['data']['id'],
                    'is_headline' => $this->input->post('isHeadline'),
                ]);
                if ($insert) {
                    Events::trigger('educations::created', $insert);
                    $this->template->build_json([
                        'success' => true,
                        'message' => lang('msg::saving_success')
                    ]);
                }
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

    public function edit_title()
    {
        userHasRoleOrDie('edit', 'educations');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('id', 'lang:lb::id', 'trim|required');
        $this->form_validation->set_rules('title', 'Title', 'trim|required|max_length[255]');
        if ($this->form_validation->run())
        {
            $id = $this->input->post('id', TRUE);
            $title = $this->input->post('title', TRUE);
            $row = $this->education_model->get(['id' => $id]);
            if (!$row) {
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);

                return false;
            }

            $update = $this->education_model->update([
                'title' => $title
            ], ['id' => $row->id]);

            if ($update) {
                Events::trigger('educations::edited', $row->id);
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

    public function mark_as_headline()
    {
        userHasRoleOrDie('edit', 'educations');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('id', 'lang:lb::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $row = $this->education_model->get(['id' => $this->input->post('id', TRUE)]);
            if (!$row) {
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);

                return false;
            }

            $update = $this->education_model->update([
                'is_headline' => '1',
            ], ['id' => $row->id]);

            if ($update) {
                Events::trigger('educations::edited', $row->id);
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

    public function disabled_as_headline()
    {
        userHasRoleOrDie('edit', 'educations');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('id', 'lang:lb::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $row = $this->education_model->get(['id' => $this->input->post('id', TRUE)]);
            if (!$row) {
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);

                return false;
            }

            $update = $this->education_model->update([
                'is_headline' => '0',
            ], ['id' => $row->id]);

            if ($update) {
                Events::trigger('educations::edited', $row->id);
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
        userHasRoleOrDie('remove', 'educations');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $row = $this->education_model->fields('id,title,file_id')->get(['id' => $id]);    // find partner
        if (!$row) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $remove = $this->education_model->delete(['id' => $row->id]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        $this->load->library('files/the_file');
        The_file::deleteFile($row->file_id);

        Events::trigger('educations::removed', $id);
        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $row->title)
        ]);

        return true;
    }
}