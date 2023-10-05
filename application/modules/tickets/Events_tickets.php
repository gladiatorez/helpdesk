<?php

class Events_tickets 
{
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();

        $this->ci->load->library('addons/the_email');
        $this->ci->load->library('telegram/the_telebot');

        Events::register('tickets::ticket_approved', array($this, 'posting_ticket'));
        // Events::register('tickets::ticket_approved', array($this, 'approve_ticket_notif_to_enduser'));
        Events::register('tickets::ticket_closed', array($this, 'close_ticket_notif_to_enduser'));
        Events::register('tickets::ticket_closed', array($this, 'index_ticket'));
        Events::register('tickets::ticket_closed', array($this, 'posting_ticket'));
        
        Events::register('tickets::add_staff', array($this, 'send_email_add_staff'));
        Events::register('tickets::ticket_response_helpdesk', array($this, 'ticket_response_helpdesk'));
        Events::register('tickets::ticket_cancellation', array($this, 'reject_ticket_notif_to_enduser'));
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

    public function approve_ticket_notif_to_enduser($ticketId)
    {
        $this->ci->load->library('tickets/the_tickets');
        $this->ci->the_tickets->notifTelegramToEndUser($ticketId, 'APPROVED');
    }

    public function reject_ticket_notif_to_enduser($ticketId)
    {
        $this->ci->load->model('tickets/tickets_view_model');
        $ticket = $this->ci->tickets_view_model->get(['id' => $ticketId]);
        if ($ticket) {
            $data = array(
                'slug' => 'ticket_reject',
                'ticket' => $ticket,
                'to' => $ticket->informer_email,
                'tiket_date_send' => date('d M Y - H:i:s', strtotime($ticket->created_at)),
                'url_access_ticket' => site_url('account#/view/' . $ticket->uid)
            );
            $this->ci->the_email->send_email($data);
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
                // Telegram notifications
                $textMessage = '*Notifikasi Penambahan Staff Ticket*';
                $textMessage .= PHP_EOL . PHP_EOL . $ticket->subject;
                $textMessage .= PHP_EOL . '#'. $ticket->number;
                $textMessage .= PHP_EOL . PHP_EOL . $ticket->description;
                $textMessage .= PHP_EOL . PHP_EOL . '_Pengirim:_';
                $textMessage .= PHP_EOL . sprintf('%s (%s)', $ticket->informer_full_name, $ticket->informer_email);
                if ($ticket->informer_phone) {
                    $textMessage .= PHP_EOL . $ticket->informer_phone;
                }
                $textMessage .= PHP_EOL . sprintf('%s (%s)', $ticket->company_name, $ticket->company_branch_name);
                $textMessage .= PHP_EOL . PHP_EOL . '_Kategori:_';
                $textMessage .= PHP_EOL . sprintf('%s > %s', $ticket->category_name, $ticket->category_sub_name);
                $textMessage .= PHP_EOL . PHP_EOL . sprintf('[Got to macca system](https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/acp???/tickets/view/%s)', $ticket->id);
                $inlineKeyboard = new \Longman\TelegramBot\Entities\InlineKeyboard([
                    [
                        'text' => 'Mark as read',
                        'callback_data' => json_encode([
                            'tiketId' => $ticket->id,
                            'command' => TICKET_EVENT_STAFF_RESPONSE
                        ])
                    ],
                ]);

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

                        // Telegram notifications
                        if ($getStaff->telegram_user) {
                            $dataMessage = [
                                'chat_id' => $getStaff->telegram_user,
                                'parse_mode' => 'MARKDOWN',
                                'text' => $textMessage,
                                'reply_markup' => $inlineKeyboard
                            ];

                            \Longman\TelegramBot\Request::sendMessage($dataMessage);
                        }
                    }
                }

                // send notif to informer
                if ($ticket->telegram_user) {
                    $getAllStaff = $this->ci->tickets_staff_view_model->get_all(['ticket_id' => $payload['ticketId']]);
                    if ($getAllStaff) {
                        $allStaff = [];
                        foreach ($getAllStaff as $rowStaff) {
                            array_push($allStaff, $rowStaff->full_name . ' (' . $rowStaff->phone . ')');
                        }

                        $informerMessage = '*Helpdesk kami telah menambahkan petugas untuk tiket yang telah anda kirim*';
                        $informerMessage .= PHP_EOL . PHP_EOL . $ticket->subject;
                        $informerMessage .= PHP_EOL . '#' . $ticket->number;
                        $informerMessage .= PHP_EOL . PHP_EOL . $ticket->description;
                        $informerMessage .= PHP_EOL . PHP_EOL . '_Kategori:_';
                        $informerMessage .= PHP_EOL . sprintf('%s > %s', $ticket->category_name, $ticket->category_sub_name);
                        $informerMessage .= PHP_EOL . PHP_EOL . '_Petugas:_';
                        $informerMessage .= PHP_EOL . implode(', ', $allStaff);
                        $informerMessage .= PHP_EOL . PHP_EOL . '[Selengkapnya](https://helpdesk.kallagroup.co.id/login)';

                        $dataMessage = [
                            'chat_id' => $ticket->telegram_user,
                            'parse_mode' => 'MARKDOWN',
                            'text' => $informerMessage,
                        ];
                        \Longman\TelegramBot\Request::sendMessage($dataMessage);
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
        if ($ticket === false) {
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
                    $logEventsFilters = array_filter($logEvents, function($row) {
                        return $row['event'] === TICKET_EVENT_HOLD;
                    });
                    $intervalHold = 0;
                    foreach ($logEventsFilters as $eventFilter) 
                    {
                        $nextEvent = array_filter($logEvents, function($row) use($eventFilter) {
                            return $row['event_from_id'] === $eventFilter['id'];
                        });
                        $nextEvent = array_values($nextEvent);
            
                        $eventHoldStart = new DateTime($eventFilter['event_date']);
                        $eventHoldEnd = new DateTime($nextEvent[0]['event_date']);
                        $interval = $eventHoldEnd->getTimestamp() - $eventHoldStart->getTimestamp();
                        $intervalHold = $intervalHold + $interval;
                    }

                    $this->ci->tickets_model->update([
                        'duration'      => $intervalOpenedClosed,
                        'duration_hold' => $intervalHold,
                        // 'date_opened' => $dateOpened,
                        'date_closed' => $dateClosed,
                    ], ['id' => $ticket->id]);
                }
            }
        }
    }

    public function posting_ticket_old2($id)
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
                    $dateTimeRequested = new DateTime($dateRequested);
                    $dateTimeHelpdeskResponse = new DateTime($dateHelpdeskResponse);
                    $intervalRequestResponse = $dateTimeHelpdeskResponse->getTimestamp() - $dateTimeRequested->getTimestamp();

                    // get duration pic response
                    $datePicResponse = null;
                    $intervalPicResponse = 0;
                    foreach ($logEvents as $event) {
                        if (empty($datePicResponse) && $event['event'] === TICKET_EVENT_STAFF_RESPONSE) {
                            $datePicResponse = $event['event_date'];

                            $prevEvent = array_filter($logEvents, function ($row) use ($event) {
                                return $row['id'] === $event['event_from_id'];
                            });
                            $prevEvent = array_values($prevEvent);

                            $eventPrevStart = new DateTime($prevEvent[0]['event_date']);
                            $eventPrevEnd = new DateTime($datePicResponse);
                            $intervalPicResponse = $eventPrevEnd->getTimestamp() - $eventPrevStart->getTimestamp();

                            break;
                        }
                    }

                    // get duration work (opened - closed)
                    $dateTimeOpened = new DateTime($dateOpened);
                    $dateTimeClosed = new DateTime($dateClosed);
                    $intervalOpenedClosed = $dateTimeClosed->getTimestamp() - $dateTimeOpened->getTimestamp();

                    // get duration work (accept PIC I - closed)
                    $datePicAccept = null;
                    $datePicAcceptEventId = 1;
                    foreach ($logEvents as $event) {
                        if (empty($datePicAccept) && $event['event'] === TICKET_EVENT_ACCEPT) {
                            $datePicAccept = $event['event_date'];
                            $datePicAcceptEventId = $event['id'];

                            break;
                        }
                    }
                    $dateTimePicAcccept = new DateTime($datePicAccept);
                    $intervalPicAcceptClosed = $dateTimeClosed->getTimestamp() - $dateTimePicAcccept->getTimestamp();

                    echo "dateTimePicAcccept = " . $dateTimePicAcccept->format('Y-m-d H:i:s')."\n";
                    echo "intervalPicAcceptClosed = " . $intervalPicAcceptClosed."\n";

                    // get duration hold
                    $logEventsFilters = array_filter($logEvents, function ($row) use ($datePicAcceptEventId) {
                        return $row['event'] === TICKET_EVENT_HOLD && $row['id'] >= $datePicAcceptEventId;
                    });
                    $logEventsFilters = array_values($logEventsFilters);
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

                        echo "eventHoldStart = " . $eventHoldStart->format('Y-m-d H:i:s')."\n";
                        echo "eventHoldEnd = " . $eventHoldEnd->format('Y-m-d H:i:s')."\n";
                        echo "interval = ".$interval."\n";
                    }
                    echo "intervalHold = ".$intervalHold."\n";

                    $this->tickets_model->update([
                        'duration' => $intervalOpenedClosed,
                        'duration_hold' => $intervalHold,
                        'duration_work' => $intervalPicAcceptClosed,
                        'response_helpdesk' => $intervalRequestResponse,
                        'response_pic' => $intervalPicResponse,
                        // 'date_opened' => $dateOpened,
                        'date_closed' => $dateClosed,
                    ], ['id' => $ticket->id]);

                    echo "==========================================================\n";
                }
            } // end if ($logEvents) 
        }
    }

    public function posting_ticket($id)
    {
        $this->ci->load->library('tickets/the_tickets');
        $this->ci->the_tickets->ticketPosting($id);
    }

    public function ticket_response_helpdesk($id)
    {
        if (isUserHelpdesk()) 
        {
            $this->ci->load->model('tickets/tickets_model');
            $this->ci->load->library('tickets/the_tickets');
            $this->ci->load->model('tickets/tickets_log_model');

            $ticket = $this->ci->tickets_model->get(['id' => $id]);
            $isRead = (bool) $ticket->is_read;
            if ($ticket && !$isRead) 
            {
                $userId = $this->ci->the_auth_backend->getUserLoginId();
                $eventId = $this->ci->the_tickets->insertEvent($id, TICKET_EVENT_RESPONSE, 'user_id:'. $userId);

                $responseEvent = $this->ci->tickets_log_model->fields('id,event_from_id,event_date')
                    ->get(['id' => $eventId]);
                if ($responseEvent) {
                    $fromEvent = $this->ci->tickets_log_model->fields('id,event_from_id,event_date')
                        ->get(['id' => $responseEvent->event_from_id]);
                    
                    $dataUpdate = ['is_read' => 1];
                    if ($fromEvent) {
                        $responseTime = strtotime($responseEvent->event_date) - strtotime($fromEvent->event_date);
                        $dataUpdate['response_helpdesk'] = $responseTime;
                    }
                    $this->ci->tickets_model->update($dataUpdate, ['id' => $id]);

                    $this->ci->the_tickets->notifTelegramToEndUser($id, 'HELPDESK_RESPONSE');
                }
            }
        }
    }
}