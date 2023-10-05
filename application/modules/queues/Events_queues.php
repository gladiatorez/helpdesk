<?php

class Events_queues 
{
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();

        $this->ci->load->library('addons/the_email');
        $this->ci->load->library('telegram/the_telebot');

        Events::register('queues::ticket_closed', array($this, 'close_ticket_notif_to_enduser'));
        // Events::register('queues::ticket_closed', array($this, 'index_ticket'));
        Events::register('queues::ticket_closed', array($this, 'posting_ticket'));

        Events::register('queues::delegation_staff', array($this, 'send_email_delegation'));
        Events::register('queues::add_staff', array($this, 'send_email_add_staff'));
        // Events::register('queues::change_flag', array($this, 'change_flag'));
        Events::register('queues::change_flag', array($this, 'posting_ticket'));

        Events::register('queues::add_comment', array($this, 'push_comment'));
//        Events::register('queues::ticket_approve_by_login', array($this, 'approve_notif_to_enduser'));
    }

    public function close_ticket_notif_to_enduser($ticketId)
    {
        $this->ci->load->model('tickets/tickets_view_model');
        $ticket = $this->ci->tickets_view_model->get(['id' => $ticketId]);
        if ($ticket) {
            $data = array(
                'slug' => 'ticket_close',
                'ticket' => $ticket,
                'to' => $ticket->informer_email,
                'tiket_date_send' => date('d M Y - H:i:s', strtotime($ticket->created_at)),
                'url_access_ticket' => site_url('account#/view/' . $ticket->uid)
            );
            $this->ci->the_email->send_email($data);
        }
    }

    public function approve_notif_to_enduser($ticketId)
    {
        $this->ci->load->library('tickets/the_tickets');
        $this->ci->the_tickets->notifTelegramToEndUser($ticketId, 'ACCEPTED');
    }

    public function send_email_delegation($payload)
    {
        $this->ci->load->model('tickets/tickets_staff_view_model');
        $this->ci->load->model('tickets/tickets_view_model');
        $ticket = $this->ci->tickets_view_model->get(['id' => $payload['ticketId']]);
        
        $staffs = $this->ci->tickets_staff_view_model->get_all(['ticket_id' => $payload['ticketId']]);
        if ($staffs && $ticket) {
            foreach ($staffs as $staff) {
                $dataEmailStaff = [
                    'slug'              => 'ticket_delegation',
                    'delegator_name'    => $this->ci->currentUser->profile->full_name,
                    'ticket'            => $ticket,
                    'staff'             => $staff,
                    'to'                => $staff->email,
                    'tiket_date_send'   => date('d M Y - H:i:s', strtotime($ticket->created_at)),
                    'url_access_ticket' => site_url_backend('#queues/assignment/view/' . $ticket->id)
                ];

                $this->ci->the_email->send_email($dataEmailStaff);
            }
        }
    }

    public function send_email_add_staff($payload)
    {
        $this->ci->load->model('tickets/tickets_staff_view_model');
        $this->ci->load->model('tickets/tickets_view_model');
        $ticket = $this->ci->tickets_view_model->get(['id' => $payload['ticketId']]);

        if (isset($payload['staff']) && is_array($payload['staff']))
        {
            $staffs = $payload['staff'];
            if ($staffs && $ticket) 
            {
                foreach ($staffs as $staff) 
                {
                    $getStaff = $this->ci->tickets_staff_view_model->get(['ticket_id' => $payload['ticketId'], 'staff_id' => $staff]);
                    if ($getStaff) 
                    {
                            $dataEmailStaff = [
                            'slug'              => 'ticket_add_staff',
                            'enhancer_name'     => $this->ci->currentUser->profile->full_name,
                            'ticket'            => $ticket,
                            'staff'             => $getStaff,
                            'to'                => $getStaff->email,
                            'tiket_date_send'   => date('d M Y - H:i:s', strtotime($ticket->created_at)),
                            'url_access_ticket' => site_url_backend('#queues/assignment/view/' . $ticket->id)
                        ];

                        $this->ci->the_email->send_email($dataEmailStaff);
                    }
                }
            }
        }
    }

    public function index_ticket($id)
    {
        $this->ci->load->model('search/search_index_model');
        $this->ci->load->model('tickets/tickets_model');
        
        $ticket = $this->ci->tickets_model->get(array('id' => $id));
        if ($ticket === FALSE) {
            $this->ci->search_index_model->drop_index('tickets', 'tickets:ticket', $id);
        } else {
            $this->ci->search_index_model->index(
                'tickets',
                'tickets:ticket',
                'tickets:tickets',
                $id,
                'account#/view/' . $ticket->uid,
                $ticket->subject,
                $ticket->description,
                array(
                    'cp_edit_uri' => BACKEND_URLPREFIX . '/tickets/edit/' . $id,
                    'keywords' => $ticket->keywords
                )
            );
        }
    }

    /**
     * Change flag
     * Apbila ticket telah finish maka akan menghitung working time dan total hold tinme
     */
    // public function change_flag($id) 
    // {
    //     $this->ci->load->model('tickets/tickets_model');
    //     $this->ci->load->model('tickets/tickets_log_model');

    //     $ticket = $this->ci->tickets_model->get(['id' => $id]);
    //     if ($ticket && $ticket->flag === TICKET_FLAG_FINISHED) 
    //     {
    //         $logEvents = $this->ci->tickets_log_model
    //             ->order_by('id')
    //             ->as_array()
    //             ->get_all(['ticket_id' => $ticket->id]);
    //         if ($logEvents) 
    //         {
    //             // begin get duration working time
    //             $filterAccept = array_filter($logEvents, function($log) {
    //                 return $log['event'] === TICKET_EVENT_ACCEPT;
    //             });
    //             $filterAccept = array_values($filterAccept);
    //             $filterAccept = $filterAccept[0];

    //             $filterFinished = array_filter($logEvents, function($log) {
    //                 return $log['event'] === TICKET_EVENT_FINISH;
    //             });
    //             $filterFinished = array_values($filterFinished);
    //             $filterFinished = end($filterFinished);

    //             $durationWorkingTime = strtotime($filterFinished['event_date']) - strtotime($filterAccept['event_date']);
    //             // end get duration working time

    //             // begin get duration hold time
    //             $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
    //                 return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept['id'];
    //             });

    //             $intervalHold = 0;
    //             foreach ($filterEventHold as $eventHold) {
    //                 $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
    //                     return $rowNext['event_from_id'] === $eventHold['id'];
    //                 });
    //                 $nextEvent = array_values($nextEvent);

    //                 $eventHoldStart = new DateTime($eventHold['event_date']);
    //                 $eventHoldEnd = new DateTime($nextEvent[0]['event_date']);
    //                 $interval = $eventHoldEnd->getTimestamp() - $eventHoldStart->getTimestamp();
    //                 $intervalHold = $intervalHold + $interval;
    //             }
    //             // end get duration hold time

    //             $this->ci->tickets_model->update([
    //                 'duration_work' => $durationWorkingTime,
    //                 'duration_hold' => $intervalHold,
    //             ], ['id' => $ticket->id]);
    //         }
    //     }
    // }

    public function posting_ticket_old($id)
    {
        $this->ci->load->model('tickets/tickets_model');
        $this->ci->load->model('tickets/tickets_log_model');

        $ticket = $this->ci->tickets_model->get(['id' => $id]);
        if ($ticket) 
        {
            $logEvents = $this->ci->tickets_log_model
                ->order_by('id')
                ->as_array()
                ->get_all(['ticket_id' => $ticket->id]);
            if ($logEvents) 
            {
                // get event opened - closed
                $dateOpened = null;
                $dateClosed = null;
                foreach ($logEvents as $event) {
                    if ($event['event'] === TICKET_EVENT_OPENED) {
                        $dateOpened = $event['event_date'];
                    }
                    if ($event['event'] === TICKET_EVENT_CLOSED) {
                        $dateClosed = $event['event_date'];
                    }
                }
                if (empty($dateOpened)) {
                    foreach ($logEvents as $event) {
                        if ($event['event'] === TICKET_EVENT_REQUEST) {
                            $dateOpened = $event['event_date'];
                        }
                    }
                }

                $this->ci->tickets_model->update([
                    'date_opened' => $dateOpened,
                ], ['id' => $ticket->id]);

                if (!empty($dateClosed))
                {
                    $dateTimeOpened = new DateTime($dateOpened);
                    $dateTimeClosed = new DateTime($dateClosed);
                    $intervalOpenedClosed = $dateTimeClosed->getTimestamp() - $dateTimeOpened->getTimestamp();

                    // get event hold
                    $logEventsFilters = array_filter($logEvents, function ($row) {
                        return $row['event'] === TICKET_EVENT_HOLD;
                    });
                    $intervalHold = 0;
                    foreach ($logEventsFilters as $eventFilter) {
                        $nextEvent = array_filter($logEvents, function ($row) use ($eventFilter) {
                            return $row['event_from_id'] === $eventFilter['id'];
                        });
                        $nextEvent = array_values($nextEvent);

                        $eventHoldStart = new DateTime($eventFilter['event_date']);
                        $eventHoldEnd = new DateTime($nextEvent[0]['event_date']);
                        $interval = $eventHoldEnd->getTimestamp() - $eventHoldStart->getTimestamp();
                        $intervalHold = $intervalHold + $interval;
                    }

                    $this->ci->tickets_model->update([
                        'duration' => $intervalOpenedClosed,
                        'duration_hold' => $intervalHold,
                        // 'date_opened' => $dateOpened,
                        'date_closed' => $dateClosed,
                    ], ['id' => $ticket->id]);
                }
            }
        }
    }

    /**
     * Posting ticket
     * Apbila ticket telah close maka akan menghitung durasi ticket dari request sampai closed
     * dan menentukan sla leadtime
     */
    public function posting_ticket_old2($id)
    {
        $this->ci->load->model('tickets/tickets_model');
        $this->ci->load->model('tickets/tickets_log_model');

        $ticket = $this->ci->tickets_model->get(['id' => $id]);
        if ($ticket && $ticket->flag === TICKET_FLAG_CLOSED) 
        {
            $logEvents = $this->ci->tickets_log_model
                ->order_by('id')
                ->as_array()
                ->get_all(['ticket_id' => $ticket->id]);
            if ($logEvents) 
            {
                $dateRequested = null;
                $dateHelpdeskResponse = null;
                $dateOpened = null;
                $dateClosed = null;
                foreach ($logEvents as $event) {
                    if ($event['event'] === TICKET_EVENT_REQUEST) {
                        $dateRequested = $event['event_date'];
                    }
                    if ($event['event'] === TICKET_EVENT_RESPONSE) {
                        $dateHelpdeskResponse = $event['event_date'];
                    }
                    if ($event['event'] === TICKET_EVENT_OPENED) {
                        $dateOpened = $event['event_date'];
                    }
                    if ($event['event'] === TICKET_EVENT_CLOSED) {
                        $dateClosed = $event['event_date'];
                    }
                }

                if (empty($dateOpened) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
                    foreach ($logEvents as $event) {
                        if ($event['event'] === TICKET_EVENT_REQUEST) {
                            $dateOpened = $event['event_date'];
                        }
                    }
                }

                if (empty($dateHelpdeskResponse) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
                    $dateHelpdeskResponse = $dateOpened;
                }

                $this->ci->tickets_model->update([
                    'date_opened' => $dateOpened,
                ], ['id' => $ticket->id]);

                if (!empty($dateClosed))
                {
                    // get duration helpdesk response
                    // $dateTimeRequested = new DateTime($dateRequested);
                    // $dateTimeHelpdeskResponse = new DateTime($dateHelpdeskResponse);
                    // $intervalRequestResponse = $dateTimeHelpdeskResponse->getTimestamp() - $dateTimeRequested->getTimestamp();

                    // // get duration pic response
                    // $datePicResponse = null;
                    // $intervalPicResponse = 0;
                    // foreach ($logEvents as $event) {
                    //     if (empty($datePicResponse) && $event['event'] === TICKET_EVENT_STAFF_RESPONSE) {
                    //         $datePicResponse = $event['event_date'];

                    //         $prevEvent = array_filter($logEvents, function ($row) use ($event) {
                    //             return $row['id'] === $event['event_from_id'];
                    //         });
                    //         $prevEvent = array_values($prevEvent);

                    //         $eventPrevStart = new DateTime($prevEvent[0]['event_date']);
                    //         $eventPrevEnd = new DateTime($datePicResponse);
                    //         $intervalPicResponse = $eventPrevEnd->getTimestamp() - $eventPrevStart->getTimestamp();

                    //         break;
                    //     }
                    // }

                    // get duration ticket (request - closed)
                    $dateTimeRequested = new DateTime($dateRequested);
                    $dateTimeClosed = new DateTime($dateClosed);
                    $intervalRequestClosed = $dateTimeClosed->getTimestamp() - $dateTimeRequested->getTimestamp();

                    // // get duration work (accept PIC I - closed)
                    // $datePicAccept = null;
                    // $datePicAcceptEventId = 1;
                    // foreach ($logEvents as $event) {
                    //     if (empty($datePicAccept) && $event['event'] === TICKET_EVENT_ACCEPT) {
                    //         $datePicAccept = $event['event_date'];
                    //         $datePicAcceptEventId = $event['id'];

                    //         break;
                    //     }
                    // }
                    // $dateTimePicAcccept = new DateTime($datePicAccept);
                    // $intervalPicAcceptClosed = $dateTimeClosed->getTimestamp() - $dateTimePicAcccept->getTimestamp();

                    // echo "dateTimePicAcccept = " . $dateTimePicAcccept->format('Y-m-d H:i:s')."\n";
                    // echo "intervalPicAcceptClosed = " . $intervalPicAcceptClosed."\n";

                    // get duration hold
                    // $logEventsFilters = array_filter($logEvents, function ($row) use ($datePicAcceptEventId) {
                    //     return $row['event'] === TICKET_EVENT_HOLD && $row['id'] >= $datePicAcceptEventId;
                    // });
                    // $logEventsFilters = array_values($logEventsFilters);
                    // $intervalHold = 0;
                    // foreach ($logEventsFilters as $eventFilter) {
                    //     $nextEvent = array_filter($logEvents, function ($row) use ($eventFilter) {
                    //         return $row['event_from_id'] === $eventFilter['id'];
                    //     });
                    //     $nextEvent = array_values($nextEvent);

                    //     $eventHoldStart = new DateTime($eventFilter['event_date']);
                    //     $eventHoldEnd = new DateTime($nextEvent[0]['event_date']);
                    //     $interval = $eventHoldEnd->getTimestamp() - $eventHoldStart->getTimestamp();
                    //     $intervalHold = $intervalHold + $interval;

                    //     echo "eventHoldStart = " . $eventHoldStart->format('Y-m-d H:i:s')."\n";
                    //     echo "eventHoldEnd = " . $eventHoldEnd->format('Y-m-d H:i:s')."\n";
                    //     echo "interval = ".$interval."\n";
                    // }
                    // echo "intervalHold = ".$intervalHold."\n";

                    $this->ci->tickets_model->update([
                        'duration' => $intervalRequestClosed,
                        // 'duration_hold' => $intervalHold,
                        // 'duration_work' => $intervalPicAcceptClosed,
                        // 'response_helpdesk' => $intervalRequestResponse,
                        // 'response_pic' => $intervalPicResponse,
                        'date_closed' => $dateClosed,
                    ], ['id' => $ticket->id]);

                    // echo "==========================================================\n";
                }
            } // end if ($logEvents) 
        }
    }

    public function posting_ticket($id)
    {
        $this->ci->load->library('tickets/the_tickets');
        $this->ci->the_tickets->ticketPosting($id);

        // tell socket
        $client = stream_socket_client($this->ci->config->item('socket_address'));
            $data_socket = [
                'channel'   => '',
                'event'     => sprintf('postingTicket'),
                'data'      => $id
        ];
        $buffer = json_encode($data_socket)."\n";

        fwrite($client, $buffer);
    }

    public function push_comment($id)
    {
        $this->ci->load->model('tickets/tickets_comment_model');
        $data = $this->ci->tickets_comment_model->getById($id);
        if ($data) 
        {
            $this->ci->load->model('tickets/tickets_staff_view_model');
            $staffs = $this->ci->tickets_staff_view_model->get_all(['ticket_id' => $data->ticket_id]);

            $this->ci->load->model('tickets/tickets_view_model');
            $ticket = $this->ci->tickets_view_model->get(['id' => $data->ticket_id]);

            // send notif telegram to staff
            if ($staffs && $ticket) {
                foreach ($staffs as $staff) {
                    if ($data->created_by_staff) {
                        $fullName = $data->created_by_staff === $staff->full_name ? 'Anda' : $data->created_by_staff;
                    } else if ($data->created_by_infomer) {
                        $fullName = $data->created_by_infomer;
                    } else {
                        $fullName = 'Administrator';
                    }
                    $tgMessage = $fullName . ' telah menambahkan komentar pada tiket #' . $ticket->number;
                    $tgMessage .= PHP_EOL . $data->comments;

                    $tgData = [
                        'chat_id' => $staff->telegram_user,
                        'parse_mode' => 'MARKDOWN',
                        'text' => $tgMessage,
                    ];

                    try {
                        \Longman\TelegramBot\Request::sendMessage($tgData);
                    } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
                        error_log('ERROR', json_encode($e));
                    }
                }
            }

            // sent notif telegram to informer
            if ($ticket && $ticket->telegram_user) {
                if ($data->$data->created_by_staff) {
                    $fullName = $data->created_by_staff;
                } else if ($data->$data->created_by_infomer) {
                    $fullName = $data->created_by_infomer === $ticket->$data->created_by_infomer ? 'Anda' : $data->created_by_infomer;
                } else {
                    $fullName = 'Administrator';
                }
                $tgMessage = $fullName . ' telah menambahkan komentar pada tiket #' . $ticket->number;
                $tgMessage .= PHP_EOL . $data->comments;

                $tgData = [
                    'chat_id' => $staff->telegram_user,
                    'parse_mode' => 'MARKDOWN',
                    'text' => $tgMessage,
                ];

                try {
                    \Longman\TelegramBot\Request::sendMessage($tgData);
                } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
                    error_log('ERROR', json_encode($e));
                }
            }


            $client = stream_socket_client($this->ci->config->item('socket_address'));

            $data_socket = [
                    'channel'   => '',
                    // 'event'     => sprintf('push-comment--' . $data->ticket_id),
                    'event'     => sprintf('pushComment'),
                    'data'      => $data
            ];
            $buffer = json_encode($data_socket)."\n";

            fwrite($client, $buffer);
        }
    }
}