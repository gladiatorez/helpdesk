<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_queues_personil_api
 *
 * @property Bt_server $bt_server
 * @property Tickets_model $tickets_model
 * @property Tickets_staff_view_model $tickets_staff_view_model
 * @property Company_model $company_model
 * @property Department_model $department_model
 * @property Category_model $category_model
 * @property The_auth_backend $the_auth_backend
 * @property The_tickets $the_tickets
 * @property Staff_view_model $staff_view_model
 * @property Closure_table $closure_table
 */
class Backend_queues_personil_api extends Backend_Api_Controller
{
    public $_section = 'personil';

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
        
        $this->load->model('staff/staff_view_model');
        $this->load->library('closure_table', [
            'table' => 'staff'
        ]);
        $request = $this->input->get();
        $getHead = $this->staff_view_model->fields('id,user_id')
            ->get(['user_id' => $this->the_auth_backend->getUserLoginId(), 'active' => '1']);
            
        
        $whereNode = [
            'user.active'   => '1',
            'd.id <>'       => $getHead->id
        ];
        $filterStaff = [];
        if (isset($request['filters'])) 
        {
            $filterStaff = array_filter($request['filters'], function($filter) {
                $filter = json_decode($filter);
                return $filter->field === 'staffId';
            });
            if ($filterStaff) {
                $filterStaff = array_values($filterStaff);
                $filterStaff = count($filterStaff) > 0 ? json_decode($filterStaff[0]) : null;
                if (isset($filterStaff->value) && $filterStaff->value && is_array($filterStaff->value)) {
                    $staffIds = [];
                    foreach ($filterStaff->value as $staffId) {
                        array_push($staffIds, $staffId);
                    }
                    
                    $whereNode[sprintf('d.id IN (%s)', implode(',',$staffIds))] = null;
                }
            }
        }

        $staffNode = $this->closure_table->getAllNode(
            $getHead->id,
            null,
            [
                'table'     => 'user_users AS user',
                'condition' => 'd.user_id = user.id',
                'type'      => 'LEFT'
            ],
            $whereNode
        );

        $staff = [];
        if ($staffNode) {
            foreach ($staffNode as $node) {
                array_push($staff, $node->id);
            }
        } 
        else {
            $this->template->build_json([
                'total'         => 0,
                'per_page'      => 0,
                'current_page'  => 0,
                'last_page'     => 0,
                'from'          => 0,
                'to'            => 0,
            ]);
            return false;
        }

        $ticketStaff = $this->tickets_staff_view_model->fields('ticket_id,user_id')
            ->where('staff_id', $staff)
            ->get_all();

        $ticketIds = array(0);
        if ($ticketStaff) {
            $ticketIds = array();
            foreach ($ticketStaff as $staff) {
                $ticketIds[] = $staff->ticket_id;
            }
        }

        $this->load->library('bt_server');
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

        if (isset($request['filters'])) {
            $filterRequest = array_filter($request['filters'], function($filter) {
                $filter = json_decode($filter);
                return $filter->field !== 'staffId';
            });
            $request['filters'] = $filterRequest;
        }
        $request = $this->bt_server->addFilterRequest('id', $ticketIds, $request, 'in');
        $results = $this->bt_server
            ->withTrashed()
            ->process($request, $columns, $this->tickets_model->table. '_view');
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

        $id = $this->input->get('id', true);
        $this->load->model('tickets/tickets_view_model');
        $item = $this->tickets_view_model
            ->as_array()
            ->with('staffs')
            ->with('logs', ['order_inside:id desc, event_date desc'])
            ->with('notes')
            ->with('attachment')
            ->get(['id' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

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

        // mengecek apakah yang login bisa mengakses data tiket.
        /*$this->load->model('staff/staff_view_model');
        $this->load->model('tickets/tickets_staff_model');
        $this->load->model('tickets/tickets_staff_view_model');
        $getHead = $this->staff_view_model->fields('id,user_id')
            ->get(['user_id' => $this->the_auth_backend->getUserLoginId(), 'active' => '1']);
        $getStaff = $this->tickets_staff_view_model->get([
            'staff_parent_id' => $getHead->id, 
            'ticket_id' => $item['id']]
        );
        if ($getStaff === false) {
            $this->output->set_status_header('400');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::access_denied')
            ]);
            return false;
        }*/

        /*if ($getStaff->is_read < 1) {
            if ($item['flag'] !== TICKET_FLAG_FINISHED || $item['flag'] !== TICKET_FLAG_CLOSED) {
                $this->load->library('tickets/the_tickets');
                $insertEvent = $this->the_tickets->insertEvent($item['id'], TICKET_EVENT_STAFF_RESPONSE, 'user_id:' . $this->the_auth_backend->getUserLoginId());
                if ($insertEvent) {
                    $this->tickets_staff_model->update(array('is_read' => 1), array('id' => $getStaff->id));
                }
                Events::trigger('ticket_staff_response', $getStaff);
            }
        }*/

        // $item['is_claimed'] = (bool)$getStaff->is_claimed;

        $data = $item;

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
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

    public function stafflist()
    {
        $this->load->model('staff/staff_view_model');
        $staffLogin = $this->staff_view_model->fields('id,user_id')
            ->get(['user_id' => $this->the_auth_backend->getUserLoginId(), 'active' => '1']);
        if (!$staffLogin) {
            $this->template->build_json([
                'staffOptions' => [],
            ]);
            return false;
        }

        $this->load->library('closure_table', [
            'table' => 'staff'
        ]);
        $staffNode = $this->closure_table->getAllNode(
            $staffLogin->id,
            null,
            [
                'table' => 'user_users AS user',
                'condition' => 'd.user_id = user.id',
                'type'  => 'LEFT'
            ],
            [
                'user.active' => '1',
                'd.id <>' => $staffLogin->id
            ]
        );

        $staff = [];
        if ($staffNode) {
            foreach ($staffNode as $node)
            {
                $staff[] = [
                    'id' => $node->id,
                    'fullName' => str_replace('-','',$node->tree)
                ];
            }
        }

        $this->template->build_json([
            'staffOptions' => $staff,
        ]);
    }
}