<?php

use Symfony\Component\EventDispatcher\Event;

(defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Class Backend_tickets_api
 *
 * @property Bt_server $bt_server
 * @property Tickets_model $tickets_model
 * @property The_tickets $the_tickets
 * @property Company_model $company_model
 * @property Department_model $department_model
 * @property Category_model $category_model
 * @property Staff_view_model $staff_view_model
 * @property Tickets_comment_model $tickets_comment_model
 * @property Tickets_part_list_model $tickets_part_list_model
 * @property Tickets_part_photo_model $tickets_part_photo_model
 * @property Tickets_work_result $tickets_work_result
 * @property CI_DB_query_builder db
 */
class Backend_tickets_api extends Backend_Api_Controller
{
    public $_section = 'all';

    public function __construct()
    {
        parent::__construct();

        $this->load->model('tickets/tickets_model');
        $this->lang->load('tickets/tickets');
    }

    public function index()
    {
        $this->load->library('bt_server');
        $this->load->model('tickets/tickets_staff_view_model');

        $ticketTable = $this->tickets_model->table . '_view';
        $ticketStaffTable = $this->tickets_staff_view_model->table;
        $request = $this->input->get();

        if (userHasRole('read', 'tickets') && !userHasRole('manage', 'tickets')) {
            $request = $this->bt_server->addFilterRequest('flag', TICKET_FLAG_CLOSED, $request);
        }

        $columns = [
            ['db' => $ticketTable . '.id', 'bt' => 'id'],
            ['db' => $ticketTable . '.uid', 'bt' => 'uid'],
            ['db' => $ticketTable . '.number', 'bt' => 'number'],
            ['db' => $ticketTable . '.subject', 'bt' => 'subject'],
            ['db' => $ticketTable . '.description', 'bt' => 'descr'],
            ['db' => $ticketTable . '.flag', 'bt' => 'flag'],
            ['db' => $ticketTable . '.company_id', 'bt' => 'companyId'],
            ['db' => $ticketTable . '.company_name', 'bt' => 'companyName'],
            ['db' => $ticketTable . '.company_branch_name', 'bt' => 'companyBranchName'],
            ['db' => $ticketTable . '.department_name', 'bt' => 'departmentName'],
            ['db' => $ticketTable . '.department_other', 'bt' => 'departmentOther'],
            ['db' => $ticketTable . '.priority_id', 'bt' => 'priorityId'],
            ['db' => $ticketTable . '.priority_name', 'bt' => 'priorityName'],
            ['db' => $ticketTable . '.informer_full_name', 'bt' => 'informerFullName'],
            ['db' => $ticketTable . '.category_id', 'bt' => 'categoryId'],
            ['db' => $ticketTable . '.category_name', 'bt' => 'categoryName'],
            ['db' => $ticketTable . '.category_sub_name', 'bt' => 'categorySubName'],
            ['db' => $ticketTable . '.created_at', 'bt' => 'createdAt'],
            ['db' => $ticketTable . '.updated_at', 'bt' => 'updatedAt'],
        ];

        $results = $this->bt_server
            ->withTrashed()
            ->setTableJoin(
                $ticketStaffTable,
                sprintf('%s.id = %s.ticket_id', $ticketTable, $ticketStaffTable),
                [
                    ['db' => $ticketStaffTable . '.staff_id', 'bt' => 'staffId'],
                ],
                'LEFT'
            )
            ->setSearchColumns(['number', 'subject', 'description','informer_full_name','company_name'])
            ->setGroupByField('id')
            ->process($request, $columns, $ticketTable);
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

        $this->load->model('tickets/tickets_view_model');
        $this->load->model('tickets/tickets_log_model');

        $id = $this->input->get('id', true);
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

        $isRead = (bool) $item['is_read'];
        if (!$isRead) {
            Events::trigger('tickets::ticket_response_helpdesk', $id);
        }

        if (isset($item['logs']) && is_array($item['logs'])) {
            foreach ($item['logs'] as &$itemLog) {
                $itemLog->userEvent = $this->tickets_log_model->getUserEvent($itemLog->event_by, $itemLog->event_by_ref_table);
                if (!empty($itemLog->event_to) && !empty($itemLog->event_to_ref_table)) {
                    $itemLog->userEventTo = $this->tickets_log_model->getUserEvent($itemLog->event_to, $itemLog->event_to_ref_table);
                }

                if (isset($itemLog->userEventTo)) {
                    $itemLog->event = sprintf(lang('tickets::log_' . $itemLog->event), $itemLog->userEventTo['fullName']);
                } else {
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

    public function category()
    {
        $this->load->model('references/category_model');
        $categories = $this->category_model
            ->order_by('name')
            ->get_all(['active' => 'A', 'estimate' => '0']);
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
        $categories = $this->category_model->order_by('name')->get_all([
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

    public function keywords_list()
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

    public function staff_list()
    {
        // get staff
        $this->load->model('staff/staff_view_model');
        $staff = $this->staff_view_model->staffHasTickets();

        $this->template->build_json([
            'staffOptions' => $staff ? $staff : [],
        ]);
    }

    public function request_reject()
    {
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('reason', 'Note', 'trim|required|min_length[5]');
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            $reject = $this->the_tickets->requestCancellation(
                $this->input->post('ticketId', TRUE),
                $this->input->post('reason', TRUE)
            );
            if ($reject) {
                Events::trigger('tickets::ticket_cancellation', $this->input->post('ticketId', TRUE));
                $this->template->build_json(array(
                    'success' => true,
                    'message' => $this->the_tickets->getMessages()
                ));
            } else {
                $this->template->build_json(array(
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                ));
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function request_approve()
    {
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('id', 'ID', 'trim|required');
        $this->form_validation->set_rules('categoryId', 'Category', 'trim|required');
        $this->form_validation->set_rules('categorySubId', 'Category sub', 'trim|required');
        $this->form_validation->set_rules('priorityId', 'Priority', 'trim|required');
        $this->form_validation->set_rules('keywords[]', 'lang:ticket_lb_keywords', 'trim|required');
        $this->form_validation->set_rules('note', 'lang:ticket_lb_note', 'trim|max_length[255]');
        if ($this->form_validation->run() === false) {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $result = array(
                'success' => false,
                'message' => $this->form_validation->error_array()
            );
        } else {
            /*$position = $this->input->post('skalaPosition') ? $this->input->post('skalaPosition') : 2;
            $job = $this->input->post('skalaJob') ? $this->input->post('skalaJob') : 2;
            $availability = $this->input->post('skalaAvailability') ? $this->input->post('skalaAvailability') : 2;
            $operation = $this->input->post('skalaOperation') ? $this->input->post('skalaOperation') : 2;
            $totalSkala = $position + $job + $availability + $operation;
            $priority = 3;
            if ($totalSkala <= 5) {
                $priority = 2;
            } else if ($totalSkala >= 6 && $totalSkala <= 9) {
                $priority = 3;
            } else if ($totalSkala >= 10) {
                $priority = 1;
            }*/
            $dataApprove = array(
                'category_id' => $this->input->post('categoryId'),
                'category_sub_id' => $this->input->post('categorySubId'),
                'estimate' => $this->input->post('estimate'),
                'keywords' => $this->input->post('keywords'),
                /*'skala_position' => $this->input->post('skalaPosition'),
                'skala_job' => $this->input->post('skalaJob'),
                'skala_availability' => $this->input->post('skalaAvailability'),
                'skala_operation' => $this->input->post('skalaOperation'),*/
                'priority_id' => $this->input->post('priorityId'),
            );
            $this->load->library('tickets/the_tickets');
            $exec = $this->the_tickets->requestApprove($this->input->post('id'), $dataApprove, $this->input->post('note'));
            if ($exec) {
                Events::trigger('tickets::ticket_approved', $this->input->post('id'));
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
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('note', 'Note', 'trim|required|min_length[5]');
        if ($this->form_validation->run()) {
            $data = [
                'ticket_id'     => $this->input->post('ticketId', true),
                'description'   => $this->input->post('note', true),
                'user_id'       => $this->the_auth_backend->getUserLoginId(),
            ];

            $this->load->model('tickets/tickets_note_model');
            $result = $this->tickets_note_model->insert($data);
            if ($result) {
                Events::trigger('tickets::add_note', $this->input->post('ticketId', true));
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
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('staff[]', 'Staff', 'trim|required');
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            if ($this->the_tickets->appendStaff($this->input->post('ticketId'), $this->input->post('staff'))) {
                Events::trigger('tickets::add_staff', array(
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
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('flag', 'Flag', 'trim|required');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|min_length[5]');
        if ($this->input->post('flag') === TICKET_FLAG_FINISHED) {
            $this->form_validation->set_rules('cause', 'Cause problem', 'trim|required');
            $this->form_validation->set_rules('solution', 'Solution', 'trim|required');
        }
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            $this->load->library('tickets/the_tickets');
            $result = $this->the_tickets->changeStatus(
                $this->input->post('ticketId'),
                $this->input->post('flag'),
                null,
                $this->input->post('reason'),
                $this->input->post('cause'),
                $this->input->post('solution'),
                $this->input->post('keywords')
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

    public function staff_remove()
    {
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('staffId', 'Staff', 'trim|required');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required');
        if ($this->form_validation->run()) {
            $this->load->library('tickets/the_tickets');
            $ticketId = $this->input->post('ticketId');
            $staffId = $this->input->post('staffId');
            $reason = $this->input->post('reason');
            if ($this->the_tickets->removeStaff($ticketId, $staffId, $reason)) {
                Events::trigger('tickets::remove_staff', array(
                    'ticketId' => $this->input->post('ticketId'), 'staffId' => $staffId
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

    public function sbulist()
    {
        $this->load->model('references/company_model');
        $companies = $this->company_model->fields(['id', 'name'])
            ->order_by('name')
            ->as_array()
            ->get_all(['active' => 'A']);
        $this->template->build_json($companies);
    }

    /**
     * Action after migrations
     */
    public function posttickets()
    {
        $tickets = $this->tickets_model
            ->where('YEAR(created_at) = 2020', NULL, NULL, FALSE, FALSE, TRUE)
            ->get_all();

        foreach ($tickets as $ticket) {
            Events::trigger('queues::ticket_closed', $ticket->id);
        }
    }

    public function add_comment()
    {
        userHasRoleOrDie('manage', 'tickets');
        
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

    public function add_partlist()
    {
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('part', 'Part', 'trim|required');
        $this->form_validation->set_rules('qty', 'Qty', 'trim|required');
        if ($this->form_validation->run()) {

            $this->load->model('tickets/tickets_part_list_model');
            $result = $this->tickets_part_list_model->create(
                $this->input->post('ticketId', true),
                $this->input->post('part', true)
            );

            if ($result) {
                Events::trigger('tickets::add_partlist', $result);
                $row = $this->tickets_part_list_model->get(['id' => $result]);
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success'),
                    'row' => $row
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

    public function remove_partlist()
    {
        userHasRoleOrDie('manage', 'tickets');

        if (!$this->input->get('id') || !$this->input->get('ticket_id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', TRUE);
        $ticketId = $this->input->get('ticket_id', TRUE);
        $this->load->model('tickets/tickets_part_list_model');
        $partList = $this->tickets_part_list_model->get(['id' => $id, 'ticket_id' => $ticketId]);    // find part list
        if (!$partList) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $remove = $this->tickets_part_list_model
            ->delete(['id' => $id, 'ticket_id' => $ticketId]);
        if (!$remove) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::delete_failed')
            ]);
            return false;
        }

        $this->template->build_json([
            'success' => true,
            'message' => sprintf(lang('msg::delete_success_fmt'), $partList->name)
        ]);

        return true;
    }

    public function add_photopart()
    {
        userHasRoleOrDie('manage', 'tickets');

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        if ($this->form_validation->run())
        {
            $this->load->library('files/the_file');
            $this->load->model('tickets/tickets_part_photo_model');

            $ticket = $this->tickets_model->get(['id' => $this->input->post('ticketId')]);
            if ($ticket) {
                $this->db->trans_start();
                $upload = The_file::upload(48, false, 'userfile', false, false, false, 'jpg|jpeg|png', $ticket->number, false, $ticket->subject);
                if (!$upload['status']) {
                    $this->db->trans_rollback();
                    $this->output->set_status_header('400', $upload['message']);
                    $this->template->build_json([
                        'success' => false,
                        'message' => $upload['message']
                    ]);
                }
                else {
                    $this->tickets_part_photo_model->insert([
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

    public function remove_photopart()
    {
        userHasRoleOrDie('manage', 'tickets');

        if (!$this->input->get('id') || !$this->input->get('ticket_id')) {
            $this->output->set_status_header('400', lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->library('files/the_file');
        $this->load->model('tickets/tickets_part_photo_model');

        $id = $this->input->get('id', TRUE);
        $ticketId = $this->input->get('ticket_id', TRUE);

        $partList = $this->tickets_part_photo_model->get(['id' => $id, 'ticket_id' => $ticketId]);    // find part list
        if (!$partList) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
        }

        $remove = $this->tickets_part_photo_model->delete(['id' => $id, 'ticket_id' => $ticketId]);
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
