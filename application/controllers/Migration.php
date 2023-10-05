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
class Migration extends MX_Controller
{
    public function import_excel()
    {

    }

//    public function index()
//    {
//        $this->load->database('old');
//        $query = $this->db
//            ->where('YEAR(created_at)', 2020)
//            ->order_by('id', 'ASC')
//            ->get('tickets');
//        $results = $query->result();
//
//        $ticketIds = [];
//        foreach ($results as $result)
//        {
//            if (!in_array($result->id, $ticketIds)) {
//                array_push($ticketIds, $result->id);
//            }
//        }
//
//        $logQuery = $this->db
//            ->where_in('ticket_id', $ticketIds)
//            ->order_by('id', 'ASC')
//            ->get('tickets_log');
//        $logResults = $logQuery->result();
//
//        $fileQuery = $this->db
//            ->where_in('ticket_id', $ticketIds)
//            ->order_by('id', 'ASC')
//            ->get('tickets_files');
//        $fileResults = $fileQuery->result();
//
//        $noteQuery = $this->db
//            ->where_in('ticket_id', $ticketIds)
//            ->order_by('id', 'ASC')
//            ->get('tickets_note');
//        $noteResults = $noteQuery->result();
//
//        $staffQuery = $this->db
//            ->where_in('ticket_id', $ticketIds)
//            ->order_by('id', 'ASC')
//            ->get('tickets_staff');
//        $staffResults = $staffQuery->result();
//        $this->db->close();
//
//        $this->load->database('default');
//        $this->db->trans_start();
//        foreach ($results as $result)
//        {
//            $logFilters = array_filter($logResults, function($log) use ($result) {
//                return $log->ticket_id === $result->id;
//            });
//            $logFilters = array_values($logFilters);
//
//            $filefilters = array_filter($fileResults, function($file) use ($result) {
//                return $file->ticket_id === $result->id;
//            });
//            $filefilters = array_values($filefilters);
//
//            $notefilters = array_filter($noteResults, function($note) use ($result) {
//                return $note->ticket_id === $result->id;
//            });
//            $notefilters = array_values($notefilters);
//
//            $staffFilters = array_filter($staffResults, function($staff) use ($result) {
//                return $staff->ticket_id === $result->id;
//            });
//            $staffFilters = array_values($staffFilters);
//
//            $this->db->insert('tickets', $result);
//            if ($filefilters) {
//                $this->db->insert_batch('tickets_files', $filefilters);
//            }
//            if ($logFilters) {
//                $this->db->insert_batch('tickets_log', $logFilters);
//            }
//            if ($notefilters) {
//                $this->db->insert_batch('tickets_note', $notefilters);
//            }
//            if ($staffFilters) {
//                $this->db->insert_batch('tickets_staff', $staffFilters);
//            }
//        }
//        $this->db->trans_complete();
//        $this->db->close();
//    }

    /**
     * Action after migrations
     */
//    public function posttickets()
//    {
//        $this->load->library('tickets/the_tickets');
//        $this->load->model('tickets/tickets_model');
//        $this->load->model('tickets/tickets_log_model');
//        $tickets = $this->tickets_model
//            ->where('YEAR(created_at) = 2020', NULL, NULL, FALSE, FALSE, TRUE)
//            ->get_all();
//
//        foreach ($tickets as $ticket)
//        {
//            echo "Begin posting".$ticket->id."\n";
//            $this->the_tickets->ticketPosting($ticket->id);
//            echo "End posting".$ticket->id."\n";
//        }
//    }

//    public function post($id)
//    {
//        $ticket = $this->tickets_model->get(['id' => $id]);
//        $logEvents = $this->tickets_log_model
//                ->order_by('id')
//                ->as_array()
//                ->get_all(['ticket_id' => $id]);
//        if ($logEvents)
//        {
//            $dateRequested = null;
//            $dateHelpdeskResponse = null;
//            $dateOpened = null;
//            $dateClosed = null;
//            foreach ($logEvents as $event) {
//                if ($event['event'] === TICKET_EVENT_REQUEST) {
//                    $dateRequested = $event['event_date'];
//                }
//                if ($event['event'] === TICKET_EVENT_RESPONSE) {
//                    $dateHelpdeskResponse = $event['event_date'];
//                }
//                if ($event['event'] === TICKET_EVENT_OPENED) {
//                    $dateOpened = $event['event_date'];
//                }
//                if ($event['event'] === TICKET_EVENT_CLOSED) {
//                    $dateClosed = $event['event_date'];
//                }
//            }
//
//            if (empty($dateOpened) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
//                foreach ($logEvents as $event) {
//                    if ($event['event'] === TICKET_EVENT_REQUEST) {
//                        $dateOpened = $event['event_date'];
//                    }
//                }
//            }
//
//            if (empty($dateHelpdeskResponse) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
//                $dateHelpdeskResponse = $dateOpened;
//            }
//
//            // if date opened empty
//            if (empty($ticket->date_opened))
//            {
//                $this->tickets_model->update([
//                    'date_opened' => $dateOpened,
//                ], ['id' => $ticket->id]);
//            }
//
//            // if ($ticket->flag === TICKET_FLAG_FINISHED)
//            // {
//                // begin get duration working time
//                $filterAccept = array_filter($logEvents, function($log) {
//                    return $log['event'] === TICKET_EVENT_ACCEPT;
//                });
//                $filterAccept = array_values($filterAccept);
//                $filterAccept = $filterAccept[0];
//
//                $filterFinished = array_filter($logEvents, function($log) {
//                    return $log['event'] === TICKET_EVENT_FINISH;
//                });
//                $filterFinished = array_values($filterFinished);
//                $filterFinished = end($filterFinished);
//
//                $durationWorkingTime = strtotime($filterFinished['event_date']) - strtotime($filterAccept['event_date']);
//                // end get duration working time
//
//                // begin get duration hold time
//                $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
//                    return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept['id'];
//                });
//
//                $intervalHold = 0;
//                foreach ($filterEventHold as $eventHold) {
//                    $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
//                        return $rowNext['event_from_id'] === $eventHold['id'];
//                    });
//                    $nextEvent = array_values($nextEvent);
//
//                    $eventHoldStart = new DateTime($eventHold['event_date']);
//                    $eventHoldEnd = new DateTime($nextEvent[0]['event_date']);
//                    $interval = $eventHoldEnd->getTimestamp() - $eventHoldStart->getTimestamp();
//                    $intervalHold = $intervalHold + $interval;
//                }
//                // end get duration hold time
//
//                $this->tickets_model->update([
//                    'duration_work' => $durationWorkingTime,
//                    'duration_hold' => $intervalHold,
//                ], ['id' => $ticket->id]);
//            // }
//
//            if ($ticket->flag === TICKET_FLAG_CLOSED && !empty($dateClosed))
//            {
//                // get duration ticket (request - closed)
//                $dateTimeRequested = new DateTime($dateRequested);
//                $dateTimeClosed = new DateTime($dateClosed);
//                $intervalRequestClosed = $dateTimeClosed->getTimestamp() - $dateTimeRequested->getTimestamp();
//
//                $this->tickets_model->update([
//                    'duration' => $intervalRequestClosed,
//                    'date_closed' => $dateClosed,
//                ], ['id' => $ticket->id]);
//            }
//
//            if (empty($ticket->response_helpdesk) && !empty($dateHelpdeskResponse))
//            {
//                $responseHelpdesk = strtotime($dateHelpdeskResponse) - strtotime($dateRequested);
//                $this->tickets_model->update([
//                    'response_helpdesk' => $responseHelpdesk,
//                ], ['id' => $ticket->id]);
//            }
//
//            // get duration pic response
//            $datePicResponse = null;
//            $intervalPicResponse = 0;
//            foreach ($logEvents as $event) {
//                if (empty($datePicResponse) && $event['event'] === TICKET_EVENT_STAFF_RESPONSE) {
//                    $datePicResponse = $event['event_date'];
//
//                    $prevEvent = array_filter($logEvents, function ($row) use ($event) {
//                        return $row['id'] === $event['event_from_id'];
//                    });
//                    $prevEvent = array_values($prevEvent);
//
//                    $eventPrevStart = new DateTime($prevEvent[0]['event_date']);
//                    $eventPrevEnd = new DateTime($datePicResponse);
//                    $intervalPicResponse = $eventPrevEnd->getTimestamp() - $eventPrevStart->getTimestamp();
//
//                    break;
//                }
//            }
//
//            $this->tickets_model->update([
//                'response_pic' => $intervalPicResponse,
//            ], ['id' => $ticket->id]);
//        }
//    }

//    public  function postbyticket()
//    {
//        require_once APPPATH . '/third_party/Carbon-1.32.0/autoload.php';
//
//        $this->load->model('tickets/tickets_model');
//        $this->load->model('tickets/tickets_log_model');
//
//        $ticket = $this->tickets_model->get(['id' => '6293']);
//        $logEvents = $this->tickets_log_model
//            ->order_by('id')
//            ->as_array()
//            ->get_all(['ticket_id' => $ticket->id]);
//
//        if ($logEvents)
//        {
//            $dateRequested = null;
//            $dateHelpdeskResponse = null;
//            $dateOpened = null;
//            $dateClosed = null;
//            foreach ($logEvents as $event) {
//                if ($event['event'] === TICKET_EVENT_REQUEST) {
//                    $dateRequested = new \Carbon\Carbon($event['event_date']);
//                }
//                if ($event['event'] === TICKET_EVENT_RESPONSE) {
//                    $dateHelpdeskResponse = new \Carbon\Carbon($event['event_date']);
//                }
//                if ($event['event'] === TICKET_EVENT_OPENED) {
//                    $dateOpened = new \Carbon\Carbon($event['event_date']);
//                }
//                if ($event['event'] === TICKET_EVENT_CLOSED) {
//                    $dateClosed = new \Carbon\Carbon($event['event_date']);
//                }
//            }
//
//            if (empty($dateOpened) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
//                foreach ($logEvents as $event) {
//                    if ($event['event'] === TICKET_EVENT_REQUEST) {
//                        $dateOpened = new \Carbon\Carbon($event['event_date']);
//                    }
//                }
//            }
//
//            if (empty($dateHelpdeskResponse) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
//                $dateHelpdeskResponse = $dateOpened;
//            }
//
//            $results = [];
//
//            $results['date_opened'] = $dateOpened->format('Y-m-d H:i:s');
//            $results['response_helpdesk'] = $dateRequested->diffInSeconds($dateHelpdeskResponse);
//
//            // get duration pic response
//            $responseStaff = 0;
//            foreach ($logEvents as $event)
//            {
//                if ($event['event'] === TICKET_EVENT_STAFF_RESPONSE)
//                {
//                    $beforeCurrentEvent = array_filter($logEvents, function ($row) use ($event) {
//                        return $row['event'] === 'TICKET_ADD_STAFF' && $row['event_to'] === $event['event_by'];
//                    });
//
//                    if (count($beforeCurrentEvent) <= 0) {
//                        continue;
//                    }
//
//                    $beforeCurrentEvent = array_values($beforeCurrentEvent);
//                    $eventStart = new \Carbon\Carbon($beforeCurrentEvent[0]['event_date']);
//                    $eventEnd = new \Carbon\Carbon($event['event_date']);
//                    $responseStaff = $eventStart->diffInSeconds($eventEnd);
//
//                    break;
//                }
//            }
//
//            $results['response_pic'] = $responseStaff;
//
//            // if ($ticket->flag === TICKET_FLAG_FINISHED)
//            // {
//                // begin get duration working time
//                $filterAccept = array_filter($logEvents, function($log) {
//                    return $log['event'] === TICKET_EVENT_ACCEPT;
//                });
//                $filterAccept = array_values($filterAccept);
//                $filterAccept = $filterAccept[0];
//                if (!$filterAccept) {
//                    $dateAccept = $dateOpened;
//                }
//                else {
//                    $dateAccept = new \Carbon\Carbon($filterAccept['event_date']);
//                }
//
//                $filterFinished = array_filter($logEvents, function($log) {
//                    return $log['event'] === TICKET_EVENT_FINISH;
//                });
//                $filterFinished = array_values($filterFinished);
//                $filterFinished = end($filterFinished);
//                $dateFinished = new \Carbon\Carbon($filterFinished['event_date']);
//
//                $durationWorkingTime = $dateAccept->diffInSeconds($dateFinished);
//                $results['duration_work_begin'] = $dateAccept;
//                $results['duration_work_end'] = $dateFinished;
//                $results['duration_work'] = $durationWorkingTime;
//                // end get duration working time
//
//                // begin get duration hold time
//                $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
//                    return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept['id'];
//                });
//                $intervalHold = 0;
//                foreach ($filterEventHold as $eventHold) {
//                    $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
//                        return $rowNext['event'] === TICKET_EVENT_PROGRESS && $rowNext['id'] > $eventHold['id'];
//                    });
//                    if (count($nextEvent) <= 0) {
//                        $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
//                            return $rowNext['event_from_id'] === $eventHold['id'];
//                        });
//                    }
//
//                    $nextEvent = array_values($nextEvent);
//                    $nextEvent = $nextEvent[0];
//
//                    $eventStart = new \Carbon\Carbon($eventHold['event_date']);
//                    $eventEnd = new \Carbon\Carbon($nextEvent['event_date']);
//                    $diffInSeconds = $eventStart->diffInSeconds($eventEnd);
//
//                    $intervalHold = $intervalHold + $diffInSeconds;
//                }
//            // }
//            $results['duration_hold'] = $intervalHold;
//            $results['leadtime'] = $durationWorkingTime - $intervalHold;
//            $results['is_achieve_sla'] = $results['leadtime'] > $ticket->estimate ? '0' : '1';
//
//            if (!empty($dateClosed))
//            {
//                // get duration ticket (request - closed)
//                $results['duration'] = $dateRequested->diffInSeconds($dateClosed);
//                $results['date_close'] = $dateClosed->format('Y-m-d H:i:s');
//            }
//
//            die(
//                json_encode($results)
//            );
//        }
//    }

//    public function export_schema()
//    {
//        $this->load->database();
//        $tableQuery = $this->db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'kalla_macca_online2' GROUP BY TABLE_NAME");
//        $tableResult = $tableQuery->result();
//
//        $spreadsheet = new Spreadsheet();
//        $sheet = $spreadsheet->getActiveSheet();
//
//        $start = 1;
//        foreach ($tableResult as $table)
//        {
//            $sheet->setCellValue('A'.$start, $table->TABLE_NAME);
//            $sheet->getStyle('A'.$start)->applyFromArray([
//                'font' => [
//                    'bold' => true,
//                ],
//            ]);
//            $sheet->mergeCells('A'.$start.':C'.$start);
//            $start++;
//
//            $schemaeQuery = $this->db->query("
//                SELECT TABLE_SCHEMA, COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY
//                FROM INFORMATION_SCHEMA.COLUMNS
//                WHERE TABLE_SCHEMA = 'kalla_macca_online2'
//                AND TABLE_NAME = '$table->TABLE_NAME';
//            ");
//            $schemaResult = $schemaeQuery->result();
//
//            $sheet->setCellValue('A'.$start, 'COLUMN_NAME');
//            $sheet->setCellValue('B'.$start, 'COLUMN_TYPE');
//            $sheet->setCellValue('C'.$start, 'COLUMN_KEY');
//            $sheet->getStyle('A'.$start.':C'.$start)->applyFromArray([
//                'font' => [
//                    'bold' => true,
//                ],
//            ]);
//            $start++;
//            foreach ($schemaResult as $schema)
//            {
//                $sheet->setCellValue('A'.$start, $schema->COLUMN_NAME);
//                $sheet->setCellValue('B'.$start, $schema->COLUMN_TYPE);
//                $sheet->setCellValue('C'.$start, $schema->COLUMN_KEY);
//                $start++;
//            }
//
//            $start++;
//        }
//
//        $writer = new Xlsx($spreadsheet);
//        $writer->save(APPPATH . 'cache/files/export_schema.xlsx');
//    }

//    public function test_posting()
//    {
//        $this->load->library('tickets/the_tickets');
//
//        $tickets = $this->tickets_model
//            ->where('created_at', '>=' ,'2021-01-01')
//            ->where('created_at', '<=' ,'2021-03-05')
//            ->get_all();
//        foreach ($tickets as $ticket) {
//            $this->the_tickets->ticketPosting($ticket->id);
//        }
//    }
}
