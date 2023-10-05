<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_reports_general_api
 *
 * @property The_tickets $the_tickets
 */
class Backend_reports_general_api extends Backend_Api_Controller
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
        $this->load->model('references/category_model');
        $categories = $this->category_model->fields('id,name')
            ->as_array()
            ->order_by('parent_id', 'ASC')
            ->with('child', ['fields:id,name'])
            ->get_all(['active' => 'A', 'parent_id' => '0']);
        if (!$categories) {
            $this->template->build_json([]);
            return false;
        }

        $categoryOptions = [];
        foreach ($categories as $category) {
            $categoryOptions[] = [
                'value' => $category['id'],
                'text' => $category['name'],
                'childrens' => $category['child']
            ];
        }

        $this->template->build_json($categoryOptions ? $categoryOptions : []);
    }
}