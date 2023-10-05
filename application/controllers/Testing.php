<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Testing
 * 
 * @property Company_model $company_model
 * @property Company_branch_model $company_branch_model
 * @property Department_model $department_model
 * @property The_auth_frontend $the_auth_frontend
 * @property The_auth_backend $the_auth_backend
 * @property Staff_model $staff_model
 * @property Tickets_model $tickets_model
 * @property Tickets_log_model $tickets_log_model
 * @property The_tickets $the_tickets
 */
class Testing extends MY_Controller 
{
	public $_themeName = 'frontend-theme';

	public function post_ticket()
    {
        $this->load->library('tickets/the_tickets');

        $tickets = $this->tickets_model->where('YEAR(created_at) = 2022', NULL, NULL, NULL, NULL, TRUE)
            ->where('MONTH(created_at) = 2', NULL, NULL, NULL, NULL, TRUE)
            ->get_all();
        foreach ($tickets as $ticket) {
            $this->the_tickets->ticketPosting($ticket->id);
        }
        die('ss');
    }

    public function test_ticket()
    {
        require_once APPPATH . '/third_party/Carbon-1.32.0/autoload.php';
        $this->load->model('tickets/tickets_model');
        $this->load->model('tickets/tickets_log_model');

        $ticket = $this->tickets_model
            ->where('flag', ['PROGRESS','FINISHED','CLOSED','HOLD'])
            ->get(['id' => 14095]);
        if ($ticket) {
            $logEvents = $this->tickets_log_model
                ->order_by('id')
                ->as_array()
                ->get_all(['ticket_id' => $ticket->id]);

            $dateRequested = null;
            $dateHelpdeskResponse = null;
            $dateOpened = null;
            $dateClosed = null;
            foreach ($logEvents as $event) {
                if ($event['event'] === TICKET_EVENT_REQUEST) {
                    $dateRequested = new \Carbon\Carbon($event['event_date']);
                }
                if ($event['event'] === TICKET_EVENT_RESPONSE) {
                    $dateHelpdeskResponse = new \Carbon\Carbon($event['event_date']);
                }
                if ($event['event'] === TICKET_EVENT_OPENED) {
                    $dateOpened = new \Carbon\Carbon($event['event_date']);
                }
                if ($event['event'] === TICKET_EVENT_CLOSED) {
                    $dateClosed = new \Carbon\Carbon($event['event_date']);
                }
            }

            $filterAccept = array_filter($logEvents, function($log) {
                return $log['event'] === TICKET_EVENT_ACCEPT;
            });
            $filterAccept = array_values($filterAccept);
            $filterAccept = $filterAccept[0];
            if (!$filterAccept) {
                $dateAccept = $dateOpened;
            }
            else {
                $dateAccept = new \Carbon\Carbon($filterAccept['event_date']);
            }

            $filterFinished = null;
            $dateFinished = new \Carbon\Carbon();
            if ($ticket->flag === TICKET_FLAG_CLOSED || $ticket->flag === TICKET_FLAG_FINISHED) {
                $filterFinishedFilter = array_filter($logEvents, function ($log) {
                    return $log['event'] === TICKET_EVENT_FINISH;
                });
                $filterFinished = array_values($filterFinishedFilter);
                $filterFinished = end($filterFinished);
                $dateFinished = new \Carbon\Carbon($filterFinished['event_date']);
            }
            $now = new \Carbon\Carbon();
            if ($filterFinished) {
                $durationWorkingTime = $dateAccept->diffInSeconds($dateFinished);
            } else {
                var_dump('no finished');
                $durationWorkingTime = $dateAccept->diffInSeconds($now);
            }
            var_dump($dateAccept->toDateTimeString());
            var_dump('$durationWorkingTime: ' . $durationWorkingTime);

            $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
                return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept['id'];
            });
            $filterEventHold = array_values($filterEventHold);
            print_r($filterEventHold);
            $intervalHold = 0;
            $lastNextEvent = 0;
            foreach ($filterEventHold as $key => $eventHold) {
                if (($key === 0) || ($lastNextEvent > 0 && $key > 0)) {
                    $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold, $lastNextEvent) {
                        if ($lastNextEvent > 0) {
                            return $rowNext['event'] === TICKET_EVENT_PROGRESS && $rowNext['id'] > $eventHold['id'] && $rowNext['id'] > $lastNextEvent;
                        } else {
                            return $rowNext['event'] === TICKET_EVENT_PROGRESS && $rowNext['id'] > $eventHold['id'];
                        }
                    });
                    if (count($nextEvent) <= 0) {
                        $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
                            return $rowNext['event'] === TICKET_EVENT_FINISH && $rowNext['id'] > $eventHold['id'];
                        });
                    }

                    $nextEvent = array_values($nextEvent);

                    // cek apakah antara finish dan hold tidak ada progress
                    $hasProgress = [];
                    if (count($nextEvent) >= 1) {
                        if ($nextEvent[0]['event'] === TICKET_EVENT_FINISH) {
                            $hasProgress = array_filter($logEvents, function ($progress) use ($eventHold, $nextEvent) {
                                return $progress['id'] > $eventHold['id'] && $progress['id'] < $nextEvent[0]['id'] && $progress['event'] === TICKET_EVENT_PROGRESS;
                            });
                        }
                    }

                    if (count($hasProgress) <= 0) {
                        $lastNextEvent = (int)$nextEvent[0]['id'];

                        $eventStart = new \Carbon\Carbon($eventHold['event_date']);
                        $eventEnd = new \Carbon\Carbon(count($nextEvent) >= 1 ? $nextEvent[0]['event_date'] : null);
                        $diffInSeconds = $eventStart->diffInSeconds($eventEnd);

                        $intervalHold = $intervalHold + $diffInSeconds;
                    }
                }
            }

            print_r('$intervalHold: ' . $intervalHold);
        }

        die();
    }
}
