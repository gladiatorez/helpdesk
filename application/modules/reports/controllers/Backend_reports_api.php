<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_reports_api
 *
 * @property The_tickets $the_tickets
 */
class Backend_reports_api extends Backend_Api_Controller
{
    public $_section = 'general';

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->load->library('bt_server');
        $this->load->model('tickets/tickets_staff_view_model');
        $this->load->model('tickets/tickets_log_model');

        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'subject', 'bt' => 'subject'],
            ['db' => 'category_name', 'bt' => 'category_name'],
            ['db' => 'category_sub_name', 'bt' => 'category_sub_name'],
            ['db' => 'id', 'bt' => 'staffs', 'formatter' => function($val) {
                $getStaff = $this->tickets_staff_view_model->get_all(['ticket_id' => $val]);
                if (!$getStaff) {
                    return null;
                }

                $staffArr = [];
                foreach ($getStaff as $staff) {
                    $staffArr[] = $staff->full_name;
                }
                return $staffArr;
            }],
            ['db' => 'id', 'bt' => 'times', 'formatter' => function ($val) {
                $getStartDate = $this->tickets_log_model->fields('event_date')
                    ->get(['ticket_id' => $val, 'event' => TICKET_EVENT_REQUEST]);
                if (!$getStartDate) {
                    return 0;
                }
                $startDateTime = strtotime($getStartDate->event_date);

                $getEndDate = $this->tickets_log_model->fields('event_date')
                    ->get(['ticket_id' => $val, 'event' => TICKET_EVENT_CLOSED]);
                if ($getEndDate) {
                    $endDateTime = strtotime($getEndDate->event_date);
                } else {
                    $endDateTime = time();
                }

                $timespan = timespan($startDateTime, $endDateTime, 2);

                return $timespan;
            }],
        ];

        $this->load->model('tickets/tickets_view_model');
        $results = $this->bt_server
            ->withTrashed()
            ->process($request, $columns, $this->tickets_view_model->table);
        $this->template->build_json($results);
    }

    public function categories()
    {
        if (!isUserAdmin()) {
            $this->load->model('references/category_staff_model');
            $staffCategories = $this->category_staff_model->fields('id,category_id,user_id')->get_all(['user_id' => $this->the_auth_backend->getUserLoginId()]);
            if (!$staffCategories) {
                $this->template->build_json([]);
                return false;
            }

            $staffCategoryIds = [];
            foreach ($staffCategories as $staffCategory) {
                if (!array_key_exists($staffCategory->category_id, $staffCategoryIds)) {
                    array_push($staffCategoryIds, $staffCategory->category_id);
                }
            }

            $this->load->model('references/category_model');
            $categories = $this->category_model->fields('id,name')
                ->where('id', $staffCategoryIds)
                ->order_by('name', 'ASC')
                ->get_all(['active' => 'A', 'parent_id <>' => '0']);
            if (!$categories) {
                $this->template->build_json([]);
                return false;
            }

            $categoryOptions = [];
            foreach ($categories as $category) {
                $categoryOptions[] = [
                    'value' => $category->id,
                    'text' => $category->name
                ];
            }

            $this->template->build_json($categoryOptions ? $categoryOptions : []);
        }
        else {
            $this->load->model('references/category_model');
            $categories = $this->category_model->fields('id,name')
                ->order_by('parent_id', 'ASC')
                ->with('top')
                ->get_all(['active' => 'A', 'parent_id <>' => '0']);
            if (!$categories) {
                $this->template->build_json([]);
                return false;
            }

            $categoryOptions = [];
            foreach ($categories as $category) {
                $categoryOptions[] = [
                    'value' => $category->id,
                    'text' => sprintf('%s / %s', $category->top->name, $category->name)
                ];
            }

            $this->template->build_json($categoryOptions ? $categoryOptions : []);
        }
    }

    public function form_options()
    {
        // get categories
        $this->load->model('references/category_model');
        $categories = $this->category_model->fields('id,name')
            ->as_array()
            ->with('child', ['fields:id,name'])
            ->order_by('name', 'asc')
            ->get_all(['active' => 'A', 'parent_id' => '0']);

        // get fields
        $fields = [
            ['value' => 'no', 'text' => 'NO'],
            ['value' => 'subject', 'text' => 'SUBJECT'],
            ['value' => 'description', 'text' => 'DESCRIPTION'],
            ['value' => 'informer_name', 'text' => 'INFORMER'],
            ['value' => 'company', 'text' => 'INFORMER'],
            ['value' => 'date_open', 'text' => 'DATE OPEN'],
            ['value' => 'date_close', 'text' => 'DATE CLOSE'],
            ['value' => 'status', 'text' => 'STATUS'],
            ['value' => 'category_path', 'text' => 'CATEGORY PATH'],
            ['value' => 'staff_name', 'text' => 'STAFF'],
            ['value' => 'sla', 'text' => 'SLA'],
            ['value' => 'length_duration', 'text' => 'DURATION'],
            ['value' => 'is_achieve_sla', 'text' => 'IS ACHIEVE SLA'],
        ];
        
        $this->template->build_json([
            'categoryOptions' => $categories ? $categories : [],
            'fieldOptions' => $fields
        ]);
    }

    public function request_reports()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('month', 'Month', 'required');
        $this->form_validation->set_rules('year', 'Year', 'required');
        $this->form_validation->set_rules('category', 'Category', 'required');
        if ($this->form_validation->run())
        {
            $data = [
                'month' => $this->input->post('month'),
                'year' => $this->input->post('year'),
                'category' => $this->input->post('category'),
            ];
            $this->load->library('tickets/the_tickets');
            $requestReport = $this->the_tickets->requestReports($data);
            if ($requestReport === false) {
                $this->output->set_status_header('400', $this->the_tickets->getErrors());
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                ]);
            } else {
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_tickets->getMessages(),
                    'file'    => $requestReport
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
}