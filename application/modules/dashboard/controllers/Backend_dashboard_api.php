<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_dashboard_api
 *
 * @property Closure_table $closure_table
 * @property The_tickets $the_tickets
 * @property CI_DB_query_builder $db
 */
class Backend_dashboard_api extends Backend_Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        
    }

    public function widget()
    {
        $this->load->library('tickets/the_tickets');

        $countStatusTicket = $this->the_tickets->countStatusTicketByDay(date('Y'), date('m'));
        
        $this->template->build_json([
            'ticket_created' => $countStatusTicket['ticket_created'],
            'ticket_progress' => $countStatusTicket['ticket_progress'],
            'ticket_hold' => $countStatusTicket['ticket_hold'],
            'ticket_close' => $countStatusTicket['ticket_finish'] + $countStatusTicket['ticket_close'],
        ]);
    }

    public function ticket_monthly()
    {
        $this->load->library('tickets/the_tickets');
        $monthly = $this->the_tickets->ticketCountMonthly();
        
        $opened = [];
        foreach ($monthly['data']['opened'] as $month) {
            array_push($opened, (int)$month);
        }
        $closed = [];
        foreach ($monthly['data']['closed'] as $month) {
            array_push($closed, (int)$month);
        }

        $this->template->build_json([
            'year' => $monthly['year'],
            'data' => [
                'opened' => $opened,
                'closed' => $closed
            ]
        ]);
    }

    public function ticket_countdown()
    {
        $this->load->library('tickets/the_tickets');
        $countDown = $this->the_tickets->ticketTickCountDown();

        $this->template->build_json([
            'rows' => $countDown['rows'],
            'count_progress' => $countDown['count_progress'],
            'count_hold' => $countDown['count_hold'],
        ]);
    }
    
    public function ticket_sbu()
    {
        $year = date('Y');
        $month = date('m');
        
        $this->load->library('tickets/the_tickets');
        $sbu = $this->the_tickets->ticketCountSBU($year, $month);
        $results = [];
        foreach ($sbu['rows'] as $row) {
            $results[] = [
                'abbr' => $row['abbr'],
                'requested' => (int) $row['cnt_request'],
                'closed' => (int) $row['cnt_closed']
            ];
        }

        $this->template->build_json([
            'year'  => $year,
            'month' => date('F'),
            'rows'  => $results
        ]);
    }

    public function ticket_category()
    {
        $year = date('Y');
        $month = date('m');
        
        $this->load->library('tickets/the_tickets');
        $categories = $this->the_tickets->ticketCountCategory($year, $month);
        
        // get parents
        $heads = array_filter($categories['rows'], function($row) {
            return $row['parent_id'] === '0';
        });
        $heads = array_values($heads);

        foreach ($heads as &$head) {
            $childs = array_filter($categories['rows'], function($child) use ($head) {
                return $child['parent_id'] === $head['id'];
            });
            $head['childs'] = array_values($childs);
        }
        
        $this->template->build_json([
            'year'  => $year,
            'month' => date('F'),
            'rows'  => $heads
        ]);
    }

    public function ticket_staff()
    {
        $year = date('Y');
        $month = date('m');
        
        $this->load->library('tickets/the_tickets');
        $tickets = $this->the_tickets->ticketCountStaff($year, $month);

        // $heads = $this->_reHierarchyStaff($tickets['rows']);

        $this->template->build_json([
            'year'  => $tickets['year'],
            'month' => date('F'),
            'rows'  => $tickets['rows']
        ]);
    }

    public function ticket()
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
        $this->lang->load('tickets/tickets');

        $id = $this->input->get('id', true);
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

    public function ticket_comments() 
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

    public function _reHierarchyStaff(array $elements, $parentId = '')
    {
        $branch = array();
        foreach ($elements as &$element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->_reHierarchyStaff($elements, $element['id']);
                if ($children) {
                    sort($children);
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public function dating()
    {
        $start = $this->input->get('start', true);
        $end = $this->input->get('end', true);
        if (!$start || !$end) {
            show_404();
        }

        $startDate = date_create($start);
        $startY = date_format($startDate, 'Y');
        $startM = date_format($startDate, 'm');
        $startD = date_format($startDate, 'd');
        $endDate = date_create($end);
        $endY = date_format($endDate, 'Y');
        $endM = date_format($endDate, 'm');
        $endD = date_format($endDate, 'd');

        $excludeCategory = Setting::get('dash_ticket_countdown_hide', 'tickets');
        $targetLeadTime = Setting::get('dash_lead_time_target', 'tickets');
        $targetServiceRate = Setting::get('dash_service_rate_target', 'tickets');

        $progressTicketQuery = $this->db->select([
            '(SELECT COUNT(*) FROM uf_tickets t1 WHERE t1.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t1.created_at) >= "'.$startY.'" AND MONTH(t1.created_at) >= "'.$startM.'" AND DAY(t1.created_at) >= "'.$startD.'") AND (YEAR(t1.created_at) <= "'.$endY.'" AND MONTH(t1.created_at) <= "'.$endM.'" AND DAY(t1.created_at) <= "'.$endD.'") ) as tiketMasuk',
            '(SELECT COUNT(*) FROM uf_tickets t2 WHERE t2.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t2.created_at) >= "'.$startY.'" AND MONTH(t2.created_at) >= "'.$startM.'" AND DAY(t2.created_at) >= "'.$startD.'") AND (YEAR(t2.created_at) <= "'.$endY.'" AND MONTH(t2.created_at) <= "'.$endM.'" AND DAY(t2.created_at) <= "'.$endD.'")  AND t2.flag = "CANCEL") as tiketCancel',
            '(SELECT COUNT(*) FROM uf_tickets t3 WHERE t3.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t3.created_at) >= "'.$startY.'" AND MONTH(t3.created_at) >= "'.$startM.'" AND DAY(t3.created_at) >= "'.$startD.'") AND (YEAR(t3.created_at) <= "'.$endY.'" AND MONTH(t3.created_at) <= "'.$endM.'" AND DAY(t3.created_at) <= "'.$endD.'")  AND t3.flag = "PROGRESS") as tiketProgress',
            '(SELECT COUNT(*) FROM uf_tickets t4 WHERE t4.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t4.created_at) >= "'.$startY.'" AND MONTH(t4.created_at) >= "'.$startM.'" AND DAY(t4.created_at) >= "'.$startD.'") AND (YEAR(t4.created_at) <= "'.$endY.'" AND MONTH(t4.created_at) <= "'.$endM.'" AND DAY(t4.created_at) <= "'.$endD.'")  AND t4.flag = "HOLD") as tiketHold',
            '(SELECT COUNT(*) FROM uf_tickets t5 WHERE t5.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t5.created_at) >= "'.$startY.'" AND MONTH(t5.created_at) >= "'.$startM.'" AND DAY(t5.created_at) >= "'.$startD.'") AND (YEAR(t5.created_at) <= "'.$endY.'" AND MONTH(t5.created_at) <= "'.$endM.'" AND DAY(t5.created_at) <= "'.$endD.'")  AND t5.flag = "FINISHED") as tiketFinished',
            '(SELECT COUNT(*) FROM uf_tickets t6 WHERE t6.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t6.created_at) >= "'.$startY.'" AND MONTH(t6.created_at) >= "'.$startM.'" AND DAY(t6.created_at) >= "'.$startD.'") AND (YEAR(t6.created_at) <= "'.$endY.'" AND MONTH(t6.created_at) <= "'.$endM.'" AND DAY(t6.created_at) <= "'.$endD.'")  AND t6.flag = "CLOSED") as tiketClosed',
        ])->get();
        $progressTicketResult = $progressTicketQuery->row();

        $requestVsClosedQuery = $this->db->select([
            '(SELECT COUNT(*) FROM uf_tickets t1 WHERE t1.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t1.created_at) >= "'.$startY.'" AND MONTH(t1.created_at) >= "'.$startM.'" AND DAY(t1.created_at) >= "'.$startD.'") AND (YEAR(t1.created_at) <= "'.$endY.'" AND MONTH(t1.created_at) <= "'.$endM.'" AND DAY(t1.created_at) <= "'.$endD.'") AND t1.date_opened IS NOT NULL ) as tiketOpened',
            '(SELECT COUNT(*) FROM uf_tickets t2 WHERE t2.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t2.created_at) >= "'.$startY.'" AND MONTH(t2.created_at) >= "'.$startM.'" AND DAY(t2.created_at) >= "'.$startD.'") AND (YEAR(t2.created_at) <= "'.$endY.'" AND MONTH(t2.created_at) <= "'.$endM.'" AND DAY(t2.created_at) <= "'.$endD.'")  AND t2.flag IN ("FINISHED","CLOSED")) as tiketClosed',
        ])->get();
        $requestVsClosedResult = $requestVsClosedQuery->row();

        $closeVsAchieveQuery = $this->db->select([
            '(SELECT COUNT(*) FROM uf_tickets t1 WHERE t1.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t1.created_at) >= "'.$startY.'" AND MONTH(t1.created_at) >= "'.$startM.'" AND DAY(t1.created_at) >= "'.$startD.'") AND (YEAR(t1.created_at) <= "'.$endY.'" AND MONTH(t1.created_at) <= "'.$endM.'" AND DAY(t1.created_at) <= "'.$endD.'") AND t1.flag IN ("FINISHED","CLOSED")) as tiketClosed',
            '(SELECT COUNT(*) FROM uf_tickets t2 WHERE t2.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t2.created_at) >= "'.$startY.'" AND MONTH(t2.created_at) >= "'.$startM.'" AND DAY(t2.created_at) >= "'.$startD.'") AND (YEAR(t2.created_at) <= "'.$endY.'" AND MONTH(t2.created_at) <= "'.$endM.'" AND DAY(t2.created_at) <= "'.$endD.'") AND (t2.estimate > t2.duration_work - t2.duration_hold) AND t2.flag IN ("FINISHED","CLOSED")) as tiketAchieve',
        ])->get();
        $closeVsAchieveResult = $closeVsAchieveQuery->row();

        $requestByCategoryQuery = $this->db->select(['category_id', 'category_name',  'COUNT(id) as count'])
            ->from('uf_tickets_view')
            ->group_start()
            ->where('category_id <>', $excludeCategory)
            ->group_end()
            ->group_start()
            ->where('YEAR(created_at) >=', $startY)
            ->where('MONTH(created_at) >=', $startM)
            ->where('DAY(created_at) >=', $startD)
            ->group_end()
            ->group_start()
            ->where('YEAR(created_at) <=', $endY)
            ->where('MONTH(created_at) <=', $endM)
            ->where('DAY(created_at) <=', $endD)
            ->group_end()
            ->group_start()
            ->where('date_opened IS NOT NULL')
            ->group_end()
            ->group_by('category_id')
            ->get();
        $requestByCategoryResult = $requestByCategoryQuery->result();

        $this->template->build_json([
            'targetLeadTime' => $targetLeadTime,
            'targetServiceRate' => $targetServiceRate,
            'progressTicket' => $progressTicketResult,
            'requestVsClosed' => $requestVsClosedResult,
            'closeVsAchieve' => $closeVsAchieveResult,
            'requestByCategory' => $requestByCategoryResult,
        ]);
    }
}