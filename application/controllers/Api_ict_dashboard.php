<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_DB_query_builder $db
 * @property The_tickets $the_tickets
 * @property Tickets_model $tickets_model
 * @property Tickets_log_model $tickets_log_model
 */
class Api_ict_dashboard extends MY_Controller
{
    public function index()
    {
        $start = $this->input->get('start', true);
        $end = $this->input->get('end', true);
        if (!$start || !$end) {
            show_404();
        }

        $startDate = date_create($start);
        $endDate = date_create($end);

        $startY = date_format($startDate, 'Y');
        $startM = date_format($startDate, 'm');
        $startD = date_format($startDate, 'd');
        $endY = date_format($endDate, 'Y');
        $endM = date_format($endDate, 'm');
        $endD = date_format($endDate, 'd');

        $this->load->library('settings/setting');
        $excludeCategory = Setting::get('dash_ticket_countdown_hide', 'tickets');
        $targetLeadTime = Setting::get('dash_lead_time_target', 'tickets');
        $targetServiceRate = Setting::get('dash_service_rate_target', 'tickets');

        $countTicketQuery = $this->db->select([
            '(SELECT COUNT(*) FROM uf_tickets t1 WHERE t1.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t1.created_at) >= "'.$startY.'" AND MONTH(t1.created_at) >= "'.$startM.'" AND DAY(t1.created_at) >= "'.$startD.'") AND (YEAR(t1.created_at) <= "'.$endY.'" AND MONTH(t1.created_at) <= "'.$endM.'" AND DAY(t1.created_at) <= "'.$endD.'") ) as ticketRequested',
            '(SELECT COUNT(*) FROM uf_tickets t2 WHERE t2.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t2.created_at) >= "'.$startY.'" AND MONTH(t2.created_at) >= "'.$startM.'" AND DAY(t2.created_at) >= "'.$startD.'") AND (YEAR(t2.created_at) <= "'.$endY.'" AND MONTH(t2.created_at) <= "'.$endM.'" AND DAY(t2.created_at) <= "'.$endD.'") AND t2.date_opened IS NOT NULL ) as ticketOpened',
            '(SELECT COUNT(*) FROM uf_tickets t3 WHERE t3.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t3.created_at) >= "'.$startY.'" AND MONTH(t3.created_at) >= "'.$startM.'" AND DAY(t3.created_at) >= "'.$startD.'") AND (YEAR(t3.created_at) <= "'.$endY.'" AND MONTH(t3.created_at) <= "'.$endM.'" AND DAY(t3.created_at) <= "'.$endD.'")  AND t3.flag IN ("FINISHED","CLOSED")) as ticketClosed',
            '(SELECT COUNT(*) FROM uf_tickets t4 WHERE t4.category_id NOT IN ('.$excludeCategory.') AND (YEAR(t4.created_at) >= "'.$startY.'" AND MONTH(t4.created_at) >= "'.$startM.'" AND DAY(t4.created_at) >= "'.$startD.'") AND (YEAR(t4.created_at) <= "'.$endY.'" AND MONTH(t4.created_at) <= "'.$endM.'" AND DAY(t4.created_at) <= "'.$endD.'") AND (t4.flag IN ("FINISHED","CLOSED")) AND (t4.estimate > t4.duration_work - t4.duration_hold)) as ticketAchieve',
        ])->get();
        $countTicketResult = $countTicketQuery->row();

        $ticketByCategoryQuery = $this->db->select(['category_id', 'category_name',  'COUNT(id) as count'])
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
        $ticketByCategoryResult = $ticketByCategoryQuery->result();
        $categories = [];
        if ($ticketByCategoryResult) {
            foreach ($ticketByCategoryResult as $item) {
                $categories[] = [
                    'category_id' => (int) $item->category_id,
                    'category_name' => $item->category_name,
                    'count' => (int) $item->count,
                ];
            }
        }

        $actualServiceRate = ((int) $countTicketResult->ticketClosed / (int) $countTicketResult->ticketOpened) * 100;
        $actualLeadTime = ((int) $countTicketResult->ticketAchieve / (int) $countTicketResult->ticketClosed) * 100;

        header("Access-Control-Allow-Origin: *");
        $this->template->build_json([
            'count' => [
                'ticketRequested' => (int) $countTicketResult->ticketRequested,
                'ticketOpened' => (int) $countTicketResult->ticketOpened,
                'ticketClosed' => (int) $countTicketResult->ticketClosed,
                'ticketAchieve' => (int) $countTicketResult->ticketAchieve,
            ],
            'byCategories' => $categories,
            'serviceRate' => [
                'target' => (float) $targetServiceRate,
                'actual' => $actualServiceRate,
            ],
            'leadTime' => [
                'target' => (float) $targetLeadTime,
                'actual' => $actualLeadTime,
            ]
        ]);
    }
}

