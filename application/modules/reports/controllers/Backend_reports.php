<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_reports_api
 *
 * @property Tickets_view_model $tickets_view_model
 * @property Tickets_log_model $tickets_log_model
 */
class Backend_reports extends Backend_Controller
{
    public $_section = 'general';

    public function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(false);
    }

    public function index()
    {
        $file = $this->input->get('file');
        if (!$file) {
            show_error('No file found');
        }

        $this->load->helper('download');
        $data = file_get_contents(APPPATH . 'cache/files/' . $file);
        unlink(APPPATH . 'cache/files/' . $file);
        force_download($file, $data);
    }

    public function by_category()
    {
        $id = $this->input->get('id');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $sub = $this->input->get('sub');
        if (!$id || !$month || !$year || !$sub) {
            show_error('Error parameter');
        }

        $this->load->model('tickets/tickets_view_model');
        $this->load->model('tickets/tickets_log_model');
        if ($sub !== 'ALL') {
            $this->tickets_view_model->where(['category_sub_id' => $sub]);
        }
        $tickets = $this->tickets_view_model
            ->with('staffs')
            ->with('logs', ['order_inside:id desc, event_date desc'])
            ->where(
                sprintf('(YEAR(created_at) = "%s" AND MONTH(created_at) = %s)', $year, $month),
                null,
                null,
                false,
                false,
                true
            )
            ->get_all(['category_id' => $id]);
        if (!$tickets) {
            show_error('Ticket list not found');
        }

        $this->load->helper('download');
        $this->lang->load('reports/reports');

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load(APPPATH . '/modules/reports/template/tracking_issue.xlsx');

        $spreadsheet->setActiveSheetIndexByName('ISSUE');
        $sheetIssue = $spreadsheet->getActiveSheet();

        $rowInc = 6;
        foreach ($tickets as $ticket) 
        {
            $sheetIssue->setCellValue('A' . $rowInc, $ticket->informer_full_name);
            $sheetIssue->setCellValue('B' . $rowInc, $ticket->informer_phone);
            // $sheetIssue->getStyle('B' . $rowInc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheetIssue->setCellValue('C' . $rowInc, "'".$ticket->informer_nik);
            $sheetIssue->getStyle('C' . $rowInc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheetIssue->setCellValue('D' . $rowInc, $ticket->informer_email);
            $sheetIssue->setCellValue('E' . $rowInc, $ticket->informer_position);
            $sheetIssue->setCellValue('F' . $rowInc, $ticket->company_name);
            $sheetIssue->setCellValue('G' . $rowInc, $ticket->department_name ? $ticket->department_name : $ticket->department_other);
            $sheetIssue->setCellValue('H' . $rowInc, $ticket->subject);
            $sheetIssue->setCellValue('I' . $rowInc, $ticket->category_name);
            $sheetIssue->setCellValue('J' . $rowInc, $ticket->category_sub_name);
            $sheetIssue->setCellValue('K' . $rowInc, $ticket->description);
            $sheetIssue->setCellValue('L' . $rowInc, $ticket->number);
            $sheetIssue->getStyle('L' . $rowInc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheetIssue->setCellValue('M' . $rowInc, $ticket->cause_descr);
            $sheetIssue->setCellValue('N' . $rowInc, $ticket->solution_descr);

            $rowLogInc = $rowInc;
            if (isset($ticket->logs) && count($ticket->logs) > 0) 
            {
                foreach ($ticket->logs as $log) {
                    $sheetIssue->setCellValue('O' . $rowLogInc, lang(sprintf('reports::event:%s', $log->event)));
                    $sheetIssue->setCellValue('P' . $rowLogInc, $log->event_date);
                    $sheetIssue->getStyle('P' . $rowLogInc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME);
                    if (!empty($log->event_by_ref_table)) {
                        $byUserEvent = $this->tickets_log_model->getUserEvent($log->event_by, $log->event_by_ref_table);
                        $sheetIssue->setCellValue('Q' . $rowLogInc, $byUserEvent ? $byUserEvent['fullName'] . ' - ' . $byUserEvent['nik'] : '');
                    }
                    if (!empty($log->event_to_ref_table)) {
                        $toUserEvent = $this->tickets_log_model->getUserEvent($log->event_to, $log->event_to_ref_table);
                        $sheetIssue->setCellValue('R' . $rowLogInc, $toUserEvent ? $toUserEvent['fullName'] . ' - ' . $toUserEvent['nik'] : '');
                    }
                    $sheetIssue->setCellValue('S' . $rowLogInc, $log->reason);

                    $rowLogInc++;
                }
            }

            $rowInc = $rowLogInc + 1;
        }

        $sheetIssue->getColumnDimension('O')->setAutoSize(true);
        $sheetIssue->getColumnDimension('P')->setAutoSize(true);
        $sheetIssue->getColumnDimension('Q')->setAutoSize(true);
        $sheetIssue->getColumnDimension('R')->setAutoSize(true);
        $sheetIssue->getColumnDimension('S')->setAutoSize(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = sprintf('Report macca - tracking issue [%s].xlsx', now());
        $writer->save(APPPATH . 'cache/files/' . $fileName);

        $data = file_get_contents(APPPATH . 'cache/files/' . $fileName);
        unlink(APPPATH . 'cache/files/' . $fileName);
        force_download(sprintf('Report macca - tracking issue [%s].xlsx', now()), $data);
    }
}