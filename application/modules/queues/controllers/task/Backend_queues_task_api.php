<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_queues_task_api
 *
 * @property Bt_server $bt_server
 * @property Tickets_model $tickets_model
 * @property Tickets_staff_view_model $tickets_staff_view_model
 * @property Tickets_comment_model $tickets_comment_model
 * @property Company_model $company_model
 * @property Department_model $department_model
 * @property Category_model $category_model
 * @property The_auth_backend $the_auth_backend
 * @property The_tickets $the_tickets
 * @property Tickets_work_result $tickets_work_result
 */
class Backend_queues_task_api extends Backend_Api_Controller
{
    public $_section = 'task';

    public function __construct()
    {
        parent::__construct();

        $this->load->model('tickets/tickets_model');
        $this->lang->load('tickets/tickets');
        $this->lang->load('queues/queues');
    }

    public function index()
    {
        $this->load->model('tickets/tickets_staff_view_model');
        $ticketStaff = $this->tickets_staff_view_model->fields('ticket_id,user_id')
            ->get_all(array('user_id' => $this->the_auth_backend->getUserLoginId()));
        $ticketIds = array(0);
        if ($ticketStaff !== false) {
            $ticketIds = array();
            foreach ($ticketStaff as $staff) {
                $ticketIds[] = $staff->ticket_id;
            }
        }

        $this->load->library('bt_server');
        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'uid', 'bt' => 'uid'],
            ['db' => 'number', 'bt' => 'number'],
            ['db' => 'subject', 'bt' => 'subject'],
            ['db' => 'description', 'bt' => 'descr'],
            ['db' => 'flag', 'bt' => 'flag'],
            ['db' => 'company_name', 'bt' => 'companyName'],
            ['db' => 'company_branch_name', 'bt' => 'companyBranchName'],
            ['db' => 'department_name', 'bt' => 'departmentName'],
            ['db' => 'department_other', 'bt' => 'departmentOther'],
            ['db' => 'priority_id', 'bt' => 'priorityId'],
            ['db' => 'priority_name', 'bt' => 'priorityName'],
            ['db' => 'informer_full_name', 'bt' => 'informerFullName'],
            ['db' => 'informer_user_id', 'bt' => 'userId'],
            ['db' => 'category_name', 'bt' => 'categoryName'],
            ['db' => 'category_sub_name', 'bt' => 'categorySubName'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $request = $this->bt_server->addFilterRequest('id', $ticketIds, $request, 'in');
        $results = $this->bt_server
            ->withTrashed()
            ->process($request, $columns, $this->tickets_model->table. '_view');
        $this->template->build_json($results);
    }

    public function services(){
        
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
        $id = $this->input->get('id', true);
        $ticket = $this->tickets_model->fields('id,flag')->get(['id' => $id]);
        if (!$ticket) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        // mengecek apakah yang login bisa mengakses data tiket.
        $this->load->model('tickets/tickets_staff_model');
        $this->load->model('tickets/tickets_staff_view_model');
        $getStaff = $this->tickets_staff_view_model->get([
            'user_id' => $this->the_auth_backend->getUserLoginId(), 
            'ticket_id' => $ticket->id]
        );
        if ($getStaff === false) {
            $this->output->set_status_header('400');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::access_denied')
            ]);
            return false;
        }

        $isRead = (bool) $getStaff->is_read;
        if (!$isRead) {
            if ($ticket->flag !== TICKET_FLAG_FINISHED || $ticket->flag !== TICKET_FLAG_CLOSED) {
                $this->load->library('tickets/the_tickets');
                $insertEvent = $this->the_tickets->insertEvent($ticket->id, TICKET_EVENT_STAFF_RESPONSE, 'user_id:' . $this->the_auth_backend->getUserLoginId());
                if ($insertEvent) 
                {
                    $dataUpdate = ['is_read' => 1];

                    $this->load->model('tickets/tickets_log_model');
                    $responseEvent = $this->tickets_log_model->fields('id,event_from_id,event_date')
                        ->get(['id' => $insertEvent]);
                    if ($responseEvent) {
                        $fromEvent = $this->tickets_log_model->fields('id,event_from_id,event_date')
                            ->get([
                                'event_to' => $this->the_auth_backend->getUserLoginId(),
                                'event' => TICKET_EVENT_ADD_STAFF,
                            ]);
                        
                        if ($fromEvent) {
                            $responseTime = strtotime($responseEvent->event_date) - strtotime($fromEvent->event_date);
                            $dataUpdate['response_time'] = $responseTime;
                        }
                    }

                    $this->tickets_staff_model->update($dataUpdate, array('id' => $getStaff->id));
                }
                Events::trigger('ticket_staff_response', $getStaff);
            }
        }

        $this->load->model('tickets/tickets_view_model');
        $item = $this->tickets_view_model
            ->as_array()
            ->with('staffs')
            ->with('logs', ['order_inside:id desc, event_date desc'])
            ->with('notes')
            ->with('attachment')
            ->with('part_list')
            ->with('part_photos')
            ->with_work_result([
                'fields' => 'id,ticket_id,file_id',
                'with' => [
                    'relation' => 'file',
                    'fields' => 'id,name,extension'
                ]
            ])
            ->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }
        $item['is_claimed'] = (bool)$getStaff->is_claimed;

        if (isset($item['logs']) && is_array($item['logs']))
        {
            $this->load->model('tickets/tickets_log_model');
            foreach ($item['logs'] as &$itemLog) {
                $itemLog->userEvent = $this->tickets_log_model->getUserEvent($itemLog->event_by, $itemLog->event_by_ref_table);
                if (!empty($itemLog->event_to) && !empty($itemLog->event_to_ref_table)) {
                    $itemLog->userEventTo = $this->tickets_log_model->getUserEvent($itemLog->event_to, $itemLog->event_to_ref_table);
                }

                if (isset($itemLog->userEventTo)) {
                    $itemLog->event = sprintf(lang('tickets::log_' . $itemLog->event), $itemLog->userEventTo['fullName']);
                }
                else {
                    $itemLog->event = lang('tickets::log_' . $itemLog->event);
                }
            }
        }

        $this->load->model('tickets/tickets_comment_model');
        $item['comments'] = $this->tickets_comment_model->getAllByTicket($item['id']);

        $data = $item;

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
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

    public function category()
    {
        $this->load->model('references/category_model');
        $categories = $this->category_model
            ->order_by('name')
            ->get_all(['active' => 'A', 'parent_id' => '0']);
        $categoriesOptions = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoriesOptions[] = [
                    'value' => $category->id,
                    'text' => $category->name
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $categoriesOptions
        ]);
    }

    public function categorysub()
    {
        $parentCategory = $this->input->get('parent_id');
        if (!$parentCategory) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->model('references/category_model');
        $categories = $this->category_model->get_all([
            'parent_id' => $parentCategory,
            'active' => 'A'
        ]);
        $categoriesOptions = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoriesOptions[] = [
                    'value' => $category->id,
                    'text' => $category->name
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $categoriesOptions
        ]);
    }

    public function priority()
    {
        $this->load->model('references/priority_model');
        $prioritys = $this->priority_model->order_by('ord')->get_all();
        $prioritysOptions = [];
        if ($prioritys) {
            foreach ($prioritys as $priority) {
                $prioritysOptions[] = [
                    'value' => $priority->id,
                    'text' => $priority->name
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $prioritysOptions
        ]);
    }

    public function staff_list()
    {
        // get staff
        $this->load->model('staff/staff_view_model');
        $staff = $this->staff_view_model->fields('id,full_name AS fullName')
            ->as_array()
            ->get_all(['active' => '1']);

        $this->template->build_json([
            'staffOptions' => $staff ? $staff : [],
        ]);
    }

    public function delegation()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('categorySubId', 'Category sub', 'trim|required');
        $this->form_validation->set_rules('reason', 'Reasosn', 'trim|required');
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            $categorySubId = $this->input->post('categorySubId');
            $reason = $this->input->post('reason');

            if ($this->the_tickets->delegationStaff($this->input->post('ticketId'), $categorySubId, $reason)) {
                Events::trigger('queues::delegation_staff', array(
                    'ticketId' => $this->input->post('ticketId'), 'categorySubId' => $categorySubId
                ));
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_tickets->getMessages()
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                ]);
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function closed_ticket()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|min_length[5]');
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            $result = $this->the_tickets->closeTicket($this->input->post('ticketId'), $this->input->post('reason'), true);
            if ($result) {
                Events::trigger('queues::close_ticket', $this->input->post('ticketId', true));
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
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function return_it()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|min_length[5]');
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            $result = $this->the_tickets->returnTicket($this->input->post('ticketId'), $this->input->post('reason'));
            if ($result) {
                Events::trigger('queues::return_it', $this->input->post('ticketId', true));
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
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function approve()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('id', 'ID', 'trim|required');
        if ($this->form_validation->run() === false) {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $result = array(
                'success' => false,
                'message' => $this->form_validation->error_array()
            );
        } else {
            $this->load->library('tickets/the_tickets');
            $exec = $this->the_tickets->acceptByStaffLogin($this->input->post('id'));
            if ($exec) {
                Events::trigger('queues::ticket_approve_by_login', $this->input->post('id'));
                $result = array(
                    'success' => true,
                    'message' => $this->the_tickets->getMessages()
                );
            } else {
                $this->output->set_status_header('400', lang('msg::saving_failed'));
                $result = array(
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                );
            }
        }

        $this->template->set_layout(false)
            ->build_json($result);
    }

    public function add_note()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('note', 'Note', 'trim|required|min_length[5]');
        if ($this->form_validation->run()) {
            $data = [
                'ticket_id' => $this->input->post('ticketId', true),
                'description' => $this->input->post('note', true),
                'user_id' => $this->the_auth_backend->getUserLoginId(),
            ];

            $this->load->model('tickets/tickets_note_model');
            $result = $this->tickets_note_model->insert($data);
            if ($result) {
                Events::trigger('queues::add_note', $this->input->post('ticketId', true));
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
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function add_staff()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('staff[]', 'Staff', 'trim|required');
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            if ($this->the_tickets->appendStaff($this->input->post('ticketId'), $this->input->post('staff'))) {
                Events::trigger('queues::add_staff', array(
                    'ticketId' => $this->input->post('ticketId'), 'staff' => $this->input->post('staff')
                ));
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_tickets->getMessages()
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                ]);
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function change_flag()
    {
        if ($this->input->post('flag') === TICKET_FLAG_CLOSED) {
            userHasRoleOrDie('close_ticket', 'queues/task');
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('flag', 'Flag', 'trim|required');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|min_length[5]');
        if ($this->input->post('flag') === TICKET_FLAG_FINISHED) {
            $this->form_validation->set_rules('cause', 'Cause problem', 'trim|required|min_length[5]');
            $this->form_validation->set_rules('solution', 'Solution', 'trim|required|min_length[5]');
        }
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            $result = $this->the_tickets->changeStatus(
                $this->input->post('ticketId'), 
                $this->input->post('flag'), 
                null, 
                $this->input->post('reason'),
                $this->input->post('cause'),
                $this->input->post('solution'),
                $this->input->post('keywords'),
                true
            );
            if ($result) {
                if ($this->input->post('flag') === TICKET_FLAG_CLOSED) {
                    Events::trigger('queues::ticket_closed', $this->input->post('ticketId', true));
                } else {
                    Events::trigger('queues::change_flag', $this->input->post('ticketId', true));
                }
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_tickets->getMessages()
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                ]);
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function add_comment()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('comments', 'comments', 'trim|required');
        if ($this->form_validation->run()) {
            $data = [
                'ticket_id' => $this->input->post('ticketId', true),
                'comments' => $this->input->post('comments', true),
            ];

            $this->load->model('tickets/tickets_comment_model');
            $result = $this->tickets_comment_model->create($data);
            if ($result) {
                Events::trigger('queues::add_comment', $result);
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
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function comments()
    {
        if (!$this->input->get('id') || !$this->input->get('ticket')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }
        $id = $this->input->get('id', true);
        $ticketId = $this->input->get('ticket', true);

        $this->load->model('tickets/tickets_comment_model');
        $comments = $this->tickets_comment_model->getAllByTicket($ticketId, $id);

        $this->template->build_json($comments);
    }

    public function add_workresult()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        if ($this->form_validation->run())
        {
            $this->load->library('files/the_file');
            $this->load->model('tickets/tickets_work_result');

            $ticket = $this->tickets_model->get(['id' => $this->input->post('ticketId')]);
            if ($ticket) {
                $this->db->trans_start();
                $upload = The_file::upload(48, false, 'userfile', false, false, false, false, $ticket->number, false, $ticket->subject);
                if (!$upload['status']) {
                    $this->db->trans_rollback();
                    $this->output->set_status_header('400', $upload['message']);
                    $this->template->build_json([
                        'success' => false,
                        'message' => $upload['message']
                    ]);
                }
                else {
                    $this->tickets_work_result->insert([
                        'ticket_id' => $ticket->id,
                        'file_id' => $upload['data']['id'],
                    ]);
                    $this->db->trans_complete();
                    if ($this->db->trans_status() === FALSE) {
                        $this->db->trans_rollback();
                        $this->output->set_status_header('400', lang('ticket_lib_request_unsuccessful'));
                        $this->template->build_json([
                            'success' => false,
                            'message' => lang('ticket_lib_request_unsuccessful')
                        ]);
                    }
                    else {
                        $this->template->build_json([
                            'success' => true,
                            'message' => 'Success added photo'
                        ]);
                    }
                }
            }
            else {
                $this->output->set_status_header('400', 'Ticket not found');
                $this->template->build_json([
                    'success' => false,
                    'message' => 'Ticket not found'
                ]);
            }
        }
    }

    public function remove_workresult()
    {
        if (!$this->input->get('id') || !$this->input->get('ticket_id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->library('files/the_file');
        $this->load->model('tickets/tickets_work_result');

        $id = $this->input->get('id', TRUE);
        $ticketId = $this->input->get('ticket_id', TRUE);

        $partList = $this->tickets_work_result->get(['id' => $id, 'ticket_id' => $ticketId]);    // find part list
        if (!$partList) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $remove = $this->tickets_work_result->delete(['id' => $id, 'ticket_id' => $ticketId]);
        The_file::deleteFile($partList->file_id);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), 'Photo of part')
        ]);

        return true;
    }
}