<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_recomendations_api
 *
 * @property Recomendations_model $recomendations_model
 * @property Recomendations_photo_model $recomendations_photo_model
 * @property Staff_view_model $staff_view_model
 */
class Backend_recomendations_api extends Backend_Api_Controller
{
    public $_section = 'read';

    protected $_validationRules = [
        ['field' => 'letter_no', 'label' => 'lang:recomendations::letter_no', 'rules' => 'trim|required|max_length[3]|callback__check_letter_no'],
        ['field' => 'letter_no_suffix', 'label' => 'lang:recomendations::letter_no', 'rules' => 'trim|required|max_length[255]'],
        ['field' => 'letter_subject', 'label' => 'lang:recomendations::letter_subject', 'rules' => 'trim|required|max_length[255]'],
        ['field' => 'letter_date', 'label' => 'lang:recomendations::letter_date', 'rules' => 'trim|required'],
        ['field' => 'ticket', 'label' => 'lang:recomendations::ticket_no', 'rules' => 'trim|required'],
        ['field' => 'descr_background', 'label' => 'lang:recomendations::descr_background', 'rules' => 'trim|required'],
        ['field' => 'descr_examination', 'label' => 'lang:recomendations::descr_examination', 'rules' => 'trim|required'],
        ['field' => 'descr_handling', 'label' => 'lang:recomendations::descr_handling', 'rules' => 'trim|required'],
        ['field' => 'descr_results', 'label' => 'lang:recomendations::descr_results', 'rules' => 'trim|required'],
        ['field' => 'descr_recomendation', 'label' => 'lang:recomendations::descr_recomendation', 'rules' => 'trim|required'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('recomendations/recomendations_model');
        $this->lang->load('recomendations/recomendations');
    }

    public function _check_letter_no($letterNo = '')
    {
        if (!empty($letterNo) && $this->input->post('letter_no_suffix')) 
        {
            $find = (bool) $this->recomendations_model
                ->where('id !=', $this->input->post('id', TRUE))
                ->count_rows([
                    'letter_no' => $letterNo,
                    'letter_no_suffix' => $this->input->post('letter_no_suffix', TRUE)
                ]);
            if ($find) 
            {
                $this->form_validation->set_message('_check_title', sprintf(lang('msg::recomendations::letter_no_already_exist'), $letterNo . $this->input->post('letter_no_suffix', TRUE)));
                return false;
            }
        }

        return true;
    }

    public function index()
    {
        $this->load->library('bt_server');

        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'letter_no', 'bt' => 'letterNo'],
            ['db' => 'letter_no_suffix', 'bt' => 'letterNoSuffix'],
            ['db' => 'letter_subject', 'bt' => 'letterSubject'],
            ['db' => 'letter_date', 'bt' => 'letterDate'],
            ['db' => 'serial_number', 'bt' => 'serial_number'],
            ['db' => 'ticket_id', 'bt' => 'ticketId'],
            ['db' => 'ticket_number', 'bt' => 'ticketNumber'],
            ['db' => 'ticket_informer_full_name', 'bt' => 'ticketInformerFullname'],
            ['db' => 'ticket_company_name', 'bt' => 'ticketCompanyName'],
            ['db' => 'ticket_department_name', 'bt' => 'ticketDepartmentName'],
            ['db' => 'ticket_company_branch_name', 'bt' => 'ticketCompanyBranchName'],
            ['db' => 'descr_background', 'bt' => 'descrBackground'],
            ['db' => 'descr_recomendation', 'bt' => 'descrRecomendation'],
            ['db' => 'maker_full_name', 'bt' => 'makerFullname'],
            ['db' => 'maker_position', 'bt' => 'makerPosition'],
            ['db' => 'approve_full_name', 'bt' => 'approveFullname'],
            ['db' => 'approve_position', 'bt' => 'approvePosition'],
            ['db' => 'approve_user_id', 'bt' => 'approveUserid'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'created_by', 'bt' => 'createdBy'],
            ['db' => 'updated_by', 'bt' => 'updatedBy'],
        ];

        $results = $this->bt_server
            ->setSearchColumns(['letter_no','letter_no_suffix','letter_subject','ticket_number','ticket_informer_full_name','serial_number'])
            ->process($request, $columns, $this->recomendations_model->table);
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
        $item = $this->recomendations_model
            ->as_array()
            ->fields(
                'id,letter_no,letter_no_suffix,letter_subject,letter_date,letter_attach,ticket_id,ticket_number,ticket_subject,ticket_informer_full_name,' .
                'ticket_company_name,ticket_department_name,ticket_company_branch_name,'.
                'descr_background,descr_examination,descr_handling,descr_results,descr_recomendation,'.
                'maker_full_name,'.
                'maker_position,approve_full_name,approve_position,approve_user_id,created_at,created_by,updated_at,updated_by','serial_number'
            )
            ->with('photos', ['fields:id,file,description'])
            ->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'row' => $item
        ]);
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

    public function approved_by()
    {
        $this->load->model('staff/staff_view_model');
        $staffMaker = $this->staff_view_model
            ->fields('id,user_id,full_name,position,parent_id')
            ->get(['user_id' => $this->_currentUser->id]);
        
        $result = [];
        if ($staffMaker) {
            $result['maker'] = [
                'full_name' => $staffMaker->full_name,
                'position' => $staffMaker->position,
            ];

            $staffApprovedBy = $this->staff_view_model
                ->fields('id,user_id,full_name,position')
                ->get(['id' => $staffMaker->parent_id]);
            if ($staffApprovedBy) {
                $result['approved'] = [
                    'full_name' => $staffApprovedBy->full_name,
                    'position' => $staffApprovedBy->position,
                ];
            }
        }
        
        $this->template->build_json($result);
    }

    public function tickets()
    {
        if (!$this->input->get('q')) {
            $this->template->build_json([]);
            return false;
        }

        $this->load->model('tickets/tickets_staff_view_model');
        $q = $this->input->get('q');
        $tickets = $this->tickets_staff_view_model->queryTicketByUserId($q);

        $this->template->build_json([
            'rows' => $tickets
        ]);
    }

    public function create()
    {
        userHasRoleOrDie('create', 'recomendations');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        if ($this->form_validation->run())
        {
            $data = [
                'letter_no' => $this->input->post('letter_no', TRUE),
                'letter_no_suffix' => $this->input->post('letter_no_suffix', TRUE),
                'letter_subject' => $this->input->post('letter_subject', TRUE),
                'letter_date' => $this->input->post('letter_date'),
                'serial_number' => $this->input->post('serial_number', TRUE),
                'ticket_id' => $this->input->post('ticket'),
                'descr_background' => $this->input->post('descr_background'),
                'descr_examination' => $this->input->post('descr_examination'),
                'descr_handling' => $this->input->post('descr_handling'),
                'descr_results' => $this->input->post('descr_results'),
                'descr_recomendation' => $this->input->post('descr_recomendation'),
            ];

            $result = $this->recomendations_model->create($data);
            if ($result['success']) {
                Events::trigger('recomendations::created', $result);
            }
            $this->template->build_json($result);
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
        userHasRoleOrDie('edit', 'recomendations');

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules($this->_validationRules);
        $this->form_validation->set_rules('id', 'lang:lb::id', 'trim|required');
        if ($this->form_validation->run())
        {
            $data = [
                'letter_no' => $this->input->post('letter_no', TRUE),
                'letter_no_suffix' => $this->input->post('letter_no_suffix', TRUE),
                'letter_subject' => $this->input->post('letter_subject', TRUE),
                'letter_date' => $this->input->post('letter_date'),
                'serial_number' => $this->input->post('serial_number', TRUE),
                'ticket_id' => $this->input->post('ticket'),
                'descr_background' => $this->input->post('descr_background'),
                'descr_examination' => $this->input->post('descr_examination'),
                'descr_handling' => $this->input->post('descr_handling'),
                'descr_results' => $this->input->post('descr_results'),
                'descr_recomendation' => $this->input->post('descr_recomendation'),
            ];

            $result = $this->recomendations_model->edit($this->input->post('id', TRUE), $data);
            if ($result['success']) {
                Events::trigger('recomendations::edited', $this->input->post('id', TRUE));
            }
            $this->template->build_json($result);
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
        userHasRoleOrDie('remove', 'recomendations');

        if (!$this->input->get('id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $recomendation = $this->recomendations_model->fields('id,letter_no,letter_no_suffix')->get(['id' => $id]);    // find partner
        if (!$recomendation) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->load->model('recomendations/recomendations_photo_model');
        $photos = $this->recomendations_photo_model->get_all(['recomendation_id' => $id]);
        if ($photos) {
            $this->load->library('files/the_file');
            foreach ($photos as $photo) {
                The_file::deleteFile($photo->file);
                $this->recomendations_photo_model
                    ->force_delete(['id' => $photo->file]);
            }
        }

        $remove = $this->recomendations_model
            ->force_delete(['id' => $this->input->get('id', TRUE)]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        Events::trigger('recomendations::removed', $this->input->get('id', true));
        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $recomendation->letter_no . $recomendation->letter_no_suffix)
        ]);

        return true;
    }

    public function add_photos()
    {
        userHasRoleOrDie('edit', 'recomendations');
        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;
        $this->form_validation->set_rules('id', 'ID', 'required');
        $this->form_validation->set_rules('fileCount', 'File count', 'is_natural_no_zero');
        if ($this->form_validation->run())
        {
            $this->load->library('files/the_file');
            $fileCount = $this->input->post('fileCount');
            $validatePhoto = true;
            $validatePhotoMsg = '';
            for ($i = 0; $i < $fileCount; $i++) {
                $checkUpload = The_file::checkUpload(49, false, 'photos_'.$i.'_file', false, false, false, 'jpg|jpeg|png');
                if (!$checkUpload['status']) {
                    $validatePhoto = false;
                    $validatePhotoMsg = $checkUpload['message'];
                }
            }
            if (!$validatePhoto) {
                $this->output->set_status_header(500, $validatePhotoMsg);
                $this->template->build_json([
                    'success' => false,
                    'message' => $validatePhotoMsg
                ]);
                return false;
            }

            $this->load->model('recomendations/recomendations_photo_model');
            $this->recomendations_photo_model->set_before_create('add_creator');
            $this->recomendations_photo_model->set_before_create('add_updater');

            for ($i = 0; $i < $fileCount; $i++) {
                $attachTmpName = $_FILES['photos_'.$i.'_file']['tmp_name'];
                if (!empty($attachTmpName)) {
                    $upload = The_file::upload(49, false, 'photos_'.$i.'_file', false, false, false, 'jpg|jpeg|png', 'Recomendation Attachment', false, 'Recomendation Attachment');
                    if ($upload['status']) {
                        $this->recomendations_photo_model->insert([
                            'recomendation_id' => $this->input->post('id'),
                            'file' => $upload['data']['id'],
                            'description' => $this->input->post('photos_'.$i.'_descr')
                        ]);
                    }
                }
            }

            Events::trigger('recomendations::attach_photos', $this->input->post('id', TRUE));
            $this->template->build_json([
                'success' => true,
                'message' => 'Photos uploaded'
            ]);
        }
        else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function remove_photo()
    {
        if (!$this->input->get('id') || !$this->input->get('recomendation')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id');
        $recomendation = $this->input->get('recomendation');

        $this->load->model('recomendations/recomendations_photo_model');
        $photo = $this->recomendations_photo_model->get(['id' => $id, 'recomendation_id' => $recomendation]);
        if (!$photo) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $this->load->library('files/the_file');
        The_file::deleteFile($photo->file);

        $remove = $this->recomendations_photo_model
            ->force_delete(['id' => $id, 'recomendation_id' => $recomendation]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        Events::trigger('recomendations::removed', $this->input->get('id', true));
        $this->template->build_json([
            'success' => true,
            'message' => lang('msg::delete_success')
        ]);
    }
}