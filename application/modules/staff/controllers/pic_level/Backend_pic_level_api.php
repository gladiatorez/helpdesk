<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_pic_level_api
 *
 * @property Pic_level_model $pic_level_model
 */
class Backend_pic_level_api extends Backend_Api_Controller
{
    public $_section = 'pic_level';

    protected $_validationRules = [
        ['field' => 'name', 'label' => 'lang:staff::pic_level:name', 'rules' => 'trim|required|max_length[255]|callback__check_name'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('staff/pic_level_model');
        $this->lang->load('staff/pic_level');
    }

    public function _check_name($name = '')
    {
        if ($this->pic_level_model->with_trashed()->check_unique_field('name', $name, $this->input->post('id'))) {
            $this->form_validation->set_message('_check_name', sprintf(lang('msg::staff::pic_level:name_already_exist'), $name));
            return false;
        }

        return true;
    }

    public function index()
    {
        $this->load->library('bt_server');
        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'name', 'bt' => 'name'],
            ['db' => 'level', 'bt' => 'level'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['name'])
            ->process($request, $columns, $this->pic_level_model->table);
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
        $item = $this->pic_level_model->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $data = [
            'id'        => $item->id,
            'name'      => $item->name,
            'level'    => $item->level,
            'createdAt' => $item->created_at,
            'updatedAt' => $item->updated_at,
        ];

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
    }

    public function create()
    {
        userHasRoleOrDie('create', 'staff', 'pic_level');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run())
        {
            $data = [
                'name'      => $this->input->post('name', TRUE),
                'level'    => $this->input->post('level', TRUE),
            ];

            $result = $this->pic_level_model->create($data);
            if ($result) {
                Events::trigger('staff::pic_level:created', $result);
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
        userHasRoleOrDie('edit', 'staff', 'pic_level');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:label::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'name'     => $this->input->post('name', TRUE),
                'level'    => $this->input->post('level', TRUE),
            ];

            $result = $this->pic_level_model->edit($this->input->post('id', true), $data);
            if ($result) {
                Events::trigger('staff::pic_level:edited', $this->input->post('id', TRUE));
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
        userHasRoleOrDie('remove', 'staff', 'pic_level');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $pic_level = $this->pic_level_model->fields('id,name')->get(['id' => $id]);    // find partner
        if (!$pic_level) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $remove = $this->pic_level_model
            ->delete(['id' => $this->input->get('id', TRUE)]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $pic_level->name)
        ]);

        return true;
    }
}