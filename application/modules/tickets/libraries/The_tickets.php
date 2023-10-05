<?php

/**
 * Class The_tickets
 *
 */
class The_tickets
{
    protected $CI;
    protected $lengthNumber = 4;

    protected $yearNumber;
    protected $monthNumber;
    protected $number;

    protected $_errors = array();
    protected $_messages = array();

    protected $messageStartDelimiter = '';
    protected $messageEndDelimiter = '';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('tickets/tickets_model');
        $this->CI->load->model('informer/informer_model');
        $this->CI->load->model('tickets/tickets_log_model');
        $this->CI->load->model('tickets/tickets_note_model');
        $this->CI->load->model('tickets/tickets_staff_model');
        $this->CI->load->model('tickets/tickets_file_model');
        $this->CI->lang->load('tickets/the_tickets');
        $this->CI->load->library('telegram/the_telebot');
    }

    /**
     * set message
     *
     * @param $message
     * @return void
     */
    public function setMessage($message, $translate = TRUE)
    {
        $this->_messages[] = $translate ? lang($message) : $message;
    }

    public function getMessages()
    {
        $_output = '';
        foreach ($this->_messages as $message) {
            $_output .= $this->messageStartDelimiter . $message . $this->messageEndDelimiter;
        }

        return $_output;
    }

    /**
     * set error
     *
     * @param $error
     * @return void
     */
    public function setError($error, $translate = TRUE)
    {
        $this->_errors[] = $translate ? lang($error) : $error;
    }

    public function getErrors()
    {
        $_output = '';
        foreach ($this->_errors as $error) {
            $_output .= $this->messageStartDelimiter . $error . $this->messageEndDelimiter;
        }

        return $_output;
    }

    public function getLastNumber($year = null, $month = null)
    {
        $year = is_null($year) ? date('Y') : $year;
        $month = is_null($month) ? date('n') : $month;

        $query = $this->CI->db->select('urt')
            ->where('YEAR(created_at)', $year)
            ->where('MONTH(created_at)', $month)
            ->order_by('urt', 'DESC')
            ->get($this->CI->tickets_model->table);
        
        $row = $query->row();
        $lastNumber = 1;
        if ($query->num_rows() > 0) {
            $lastNumber = $row->urt + 1;
        }

        return [
            'year' => $year,
            'month' => $month,
            'number' => $lastNumber
        ];
    }

    public function generateNumber()
    {
        // format number ticket year[4].month[2].number[4]

        $last = $this->getLastNumber();

        $this->yearNumber = $last['year'];
        $this->monthNumber = $last['month'];
        $this->number = $last['number'];

        $month = str_pad($this->monthNumber, 2, '0', STR_PAD_LEFT);
        $number = str_pad($this->number, $this->lengthNumber, '0', STR_PAD_LEFT);

        return $this->yearNumber . $month . $number;
    }

    public function insertEvent($ticketId, $event, $eventBy, $eventTo = null, $reason = null)
    {
        $eventBy = explode(':', $eventBy);
        $eventTo = $eventTo ? explode(':', $eventTo) : '';

        $data = array();
        if ($eventTo)
        {
            $eventToArr = explode(',', $eventTo[1]);
            foreach ($eventToArr as $value) {
                // get last event from ticket
                $lastEvent = $this->CI->tickets_log_model
                    ->order_by('id', 'DESC')
                    ->get(array('ticket_id' => $ticketId));

                $data[] = array(
                    'ticket_id' => $ticketId,
                    'event' => $event,
                    'event_by' => $eventBy ? $eventBy[1] : null,
                    'event_by_ref_table' => $eventBy[0] == 'user_id' ? 'users/user_model' : 'informer/informer_model',
                    'event_to' => $value,
                    'event_to_ref_table' => $eventTo[0] == 'user_id' ? 'users/user_model' : 'informer/informer_model',
                    'reason' => $reason,
                    'event_from_id' => $lastEvent ? $lastEvent->id : 0
                );
            }
        }
        else {
            // get last event from ticket
            $lastEvent = $this->CI->tickets_log_model
                ->order_by('id', 'DESC')
                ->get(array('ticket_id' => $ticketId));

            $data = array(
                'ticket_id' => $ticketId,
                'event' => $event,
                'event_by' => $eventBy ? $eventBy[1] : null,
                'event_by_ref_table' => $eventBy[0] == 'user_id' ? 'users/user_model' : 'informer/informer_model',
                'event_to' => null,
                'event_to_ref_table' => null,
                'reason' => $reason,
                'event_from_id' => $lastEvent ? $lastEvent->id : 0
            );
        }

        return $this->CI->tickets_log_model->insert($data);
    }

    public function requestTicket($dataTicket, $email, $sentEmail = true, $filesCount = 0, $sbu, $phone)
    {
        if (!array_key_exists('subject', $dataTicket) || !array_key_exists('description', $dataTicket) ||
            !array_key_exists('category_id', $dataTicket) || !array_key_exists('category_sub_id', $dataTicket)) {
            $this->setError('ticket_lib_error_validation_field');
            return false;
        }

        if (!array_key_exists('number', $dataTicket)) {
            $lasNumber = $this->generateNumber();
            $dataTicket['number'] = $lasNumber;
            $dataTicket['urt'] = $this->number;
        }

        // get informer by email
        $this->CI->load->model('informer/informer_view_model');
        $this->CI->load->model('informer/informer_model');
        $informer = $this->CI->informer_view_model->get(['email' => $email]);
        if (!$informer) {
            $this->setError('ticket_lib_sender_not_found');
            return false;
        }

        if ($sbu <> 0) {
            $this->CI->informer_model->update(['company_id' => $sbu], array('id' => $informer->id));
        }

        if ($phone) {
            $this->CI->informer_model->update(['phone' => $phone], array('id' => $informer->id));
        }

        if (empty($informer->company_id) || empty($informer->company_branch_id) || 
            empty($informer->department_id) || empty($informer->department_id) ) 
        {
            $this->setError('ticket_lib_sender_profile_not_complete');
            return false;
        }

        $dataTicket['informer_id'] = $informer->id;
        $dataTicket['company_id'] = $informer->company_id;
        $dataTicket['company_branch_id'] = $informer->company_branch_id;
        $dataTicket['department_id'] = $informer->department_id;
        if ($informer->department_id === '-1') {
            $dataTicket['department_other'] = $informer->department_other;
        }

        //update nik and phone number informer
        $this->CI->load->model('references/category_model');

        // get sub category information
        $this->CI->load->model('references/category_model');
        $subCategory = $this->CI->category_model
            ->with('staff')
            ->get(['id' => $dataTicket['category_sub_id'], 'parent_id' => $dataTicket['category_id'], 'active' => 'A']);
        if (!$subCategory) {
            $this->setError('ticket_lib_category_not_found');
            return false;
        }
        $dataTicket['estimate'] = $subCategory->estimate;
        $dataTicket['priority_id'] = $subCategory->auto_priority;

        $this->CI->db->trans_start();

        $dataTicket['flag'] = TICKET_FLAG_REQUESTED;
        $dataTicket['uid'] = genUid();
        $dataTicket['estimate_response_time'] = Setting::get('response_time', 'tickets');
        
        $insertTicket = $this->CI->tickets_model->insert($dataTicket);
        if (!$insertTicket) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_lib_request_unsuccessful');
            return false;
        }
        $this->insertEvent($insertTicket, TICKET_EVENT_REQUEST, 'end_user:' . $informer->id);

        $this->CI->load->library('files/the_file');
        $uploadedFiles = [];
        if ($filesCount > 0) {
            $uploadState = true;
            $uploadStateMsg = '';
            for ($i = 1; $i <= $filesCount; $i++) {
                $attachTmpName = $_FILES['userfiles_' . $i]['tmp_name'];
                if (!empty($attachTmpName)) {
                    $upload = The_file::upload(48, false, 'userfiles_' . $i, false, false, false, 'jpg|jpeg|png|doc|docx|xls|xlsx|pdf', $dataTicket['number'], false, $dataTicket['subject']);
                    if (!$upload['status']) {
                        $uploadState = false;
                        $uploadStateMsg = $upload['message'];
                        break;
                    } else {
                        $uploadedFiles[] = $upload['data']['id'];
                    }
                }
            }
            if (!$uploadState) {
                $this->CI->db->trans_rollback();
                $this->setError($uploadStateMsg);
                return false;
            }
        }
        foreach ($uploadedFiles as $file) {
            $this->CI->tickets_file_model->insert([
                'ticket_id' => $insertTicket,
                'file_id' => $file
            ]);
        }

        $autoSendStaff = (bool)$subCategory->auto_send_staff;
        if ($autoSendStaff) {
            $this->CI->load->model('staff/staff_view_model');
            if (isset($subCategory->staff) && count($subCategory->staff)) 
            {
                $this->insertEvent($insertTicket, TICKET_EVENT_OPENED, 'user_id:1', null, 'Ticket opened by system');
                $countAddStaff = 0;
                foreach ($subCategory->staff as $staff) 
                {
                    $find = $this->CI->staff_view_model->get(['user_id' => $staff->user_id, 'active' => '1']);
                    if ($find) {
                        $countAddStaff += 1;
                        $this->CI->tickets_staff_model->insert([
                            'ticket_id' => $insertTicket,
                            'staff_id' => $find->id,
                            'is_read' => '0'
                        ]);
                        $this->insertEvent($insertTicket, TICKET_EVENT_ADD_STAFF, 'user_id:1', 'user_id:' . $find->user_id);
                    }
                }
            }
        }

        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_lib_request_unsuccessful');
            return false;
        }

        if ($sentEmail) {
            $this->CI->load->library('addons/the_email');
            $ticket = $this->CI->tickets_model
                ->with('informer')
                ->with('staff')
                ->get(array('id' => $insertTicket));
            if ($ticket) {
                $data = array(
                    'slug' => 'ticket_request',
                    'ticket' => $ticket,
                    'to' => $informer->email,
                    'tiket_date_send' => date('d M Y - H:i:s', strtotime($ticket->created_at)),
                    'url_access_ticket' => site_url('tickets?q=' . $ticket->number)
                );
                $this->CI->the_email->send_email($data);

                if ($autoSendStaff) {
                    if (isset($ticket->staff) && count($ticket->staff)) {
                        $this->CI->load->model('staff/staff_model');

                        $countAddStaff = 0;
                        foreach ($ticket->staff as $staff) {
                            $staff = $this->CI->staff_model
                                ->with('profile')
                                ->with('user')
                                ->get(['id' => $staff->staff_id]);
                            if ($staff) {
                                $countAddStaff += 1;
                                $dataEmailStaff = [
                                    'slug'              => 'ticket_add_staff',
                                    'ticket'            => $ticket,
                                    'staff'             => $staff,
                                    'to'                => $staff->user->email,
                                    'tiket_date_send'   => date('d M Y - H:i:s', strtotime($ticket->created_at)),
                                    'url_access_ticket' => site_url_backend('#queues/view/' . $ticket->id)
                                ];

                                $this->CI->the_email->send_email($dataEmailStaff);
                            }
                        }

                        if ($countAddStaff > 0) {
                            $this->changeStatus($insertTicket, TICKET_FLAG_HOLD, '1', 'Waiting response from staff');
                        }
                    }
                }
            }
        }

        /** Send notification with telegram */
        $this->notifTelegramRequestTicket($insertTicket);
        $this->notifTelegramToEndUser($insertTicket, 'REQUESTED');

        $this->setMessage('ticket_lib_request_success');
        return $insertTicket;
    }

    public function requestApprove($id, $dataApprove, $note = null)
    {
        if (!array_key_exists('category_id', $dataApprove) || !array_key_exists('keywords', $dataApprove) ||
            !array_key_exists('category_sub_id', $dataApprove) || !array_key_exists('priority_id', $dataApprove)) {
            $this->setError('ticket_lib_error_validation_field');
            return false;
        }

        $this->CI->db->trans_start();

        $this->CI->load->library('references/keyword');
        $keywords = Keyword::process($dataApprove['keywords']);

        $dataApprove['keywords'] = $keywords;
        $dataApprove['flag'] = TICKET_FLAG_OPENED;

        $userId = $this->CI->the_auth_backend->getUserLoginId();

        $this->CI->tickets_model->update($dataApprove, array('id' => $id));
        $this->insertEvent($id, TICKET_EVENT_APPROVE, 'user_id:' . $userId);
        $this->insertEvent($id, TICKET_EVENT_OPENED, 'user_id:' . $userId);

        if ($note) {
            $this->CI->tickets_note_model->insert(array(
                'ticket_id' => $id,
                'user_id' => $userId,
                'description' => $note
            ));
        }

        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_lib_approve_unsuccessful');
            return false;
        }

        $this->setMessage('ticket_lib_approve_success');
        return true;
    }

    public function requestAccess($email, $company, $department, $section)
    {
        $sender = $this->CI->ticket_sender_model->get(array(
            'email' => $email,
            'company_id' => $company,
            'department_id' => $department === 'others' ? '0' : $department,
            'section_id' => $section === 'others' ? '0' : $section
        ));

        if ($sender === FALSE) {
            $this->setError('ticket_lib_sender_not_found');
            return false;
        }

        $ticket = $this->CI->tickets_model->with('sender')->get(array('sender_id' => $sender->id));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $this->CI->load->library('addons/the_email');
        $data = array(
            'slug' => 'ticket_access_link',
            'ticket' => $ticket,
            'to' => $sender->email,
            'url_access_ticket' => site_url('tickets?q='.$ticket->number)
        );
        if ($this->CI->the_email->send_email($data)) {
            $this->setMessage('ticket_lib_request_access_success');
            return true;
        } else {
            $this->setError('ticket_lib_request_access_failed');
            return false;
        }
    }

    public function acceptByStaffLogin($ticketId)
    {
        $userId = $this->CI->the_auth_backend->getUserLoginId();
        if ($this->CI->tickets_model->get(array('id' => $ticketId))) 
        {
            $this->CI->load->model('tickets/tickets_staff_view_model');
            $find = $this->CI->tickets_staff_view_model->get([
                'ticket_id' => $ticketId,
                'user_id'  => $userId,
            ]);
            if (!$find) {
                $this->setError('ticket_lib_request_failed');
                return false;
            }

            if ($find->is_claimed > 0) {
                $this->setError('ticket_lib_claim_already');
                return false;
            }

            $this->CI->load->model('tickets/tickets_staff_model');

            $this->CI->db->trans_start();

            $this->CI->tickets_staff_model->update(['is_claimed' => '1'], [
                'ticket_id' => $ticketId,
                'staff_id' => $find->staff_id
            ]);
            $this->insertEvent($ticketId, TICKET_EVENT_ACCEPT, 'user_id:' . $userId);
            
            $this->CI->tickets_model->update(array(
                'flag' => TICKET_FLAG_PROGRESS
            ), array('id' => $ticketId));
            $this->insertEvent($ticketId, TICKET_EVENT_PROGRESS, 'user_id:' . $userId);

            $this->CI->db->trans_complete();
            if ($this->CI->db->trans_status() === false) {
                $this->CI->db->trans_rollback();
                $this->setError('ticket_msg_claim_failed');
                return false;
            }

            $this->setMessage('ticket_lib_claim_success');
            return true;
        }
    }

    public function acceptByStaffUserId($ticketId, $userId)
    {
        if ($this->CI->tickets_model->get(array('id' => $ticketId)))
        {
            $this->CI->load->model('tickets/tickets_staff_view_model');
            $find = $this->CI->tickets_staff_view_model->get([
                'ticket_id' => $ticketId,
                'user_id'  => $userId,
            ]);
            if (!$find) {
                $this->setError('ticket_lib_request_failed');
                return false;
            }

            if ($find->is_claimed > 0) {
                $this->setError('ticket_lib_claim_already');
                return false;
            }

            $this->CI->load->model('tickets/tickets_staff_model');

            $this->CI->db->trans_start();

            $this->CI->tickets_staff_model->update(['is_claimed' => '1'], [
                'ticket_id' => $ticketId,
                'staff_id' => $find->staff_id
            ]);
            $this->insertEvent($ticketId, TICKET_EVENT_ACCEPT, 'user_id:' . $userId);

            $this->CI->tickets_model->update(array(
                'flag' => TICKET_FLAG_PROGRESS
            ), array('id' => $ticketId));
            $this->insertEvent($ticketId, TICKET_EVENT_PROGRESS, 'user_id:' . $userId);

            $this->CI->db->trans_complete();
            if ($this->CI->db->trans_status() === false) {
                $this->CI->db->trans_rollback();
                $this->setError('ticket_msg_claim_failed');
                return false;
            }

            $this->setMessage('ticket_lib_claim_success');
            // $this->notifTelegramToEndUser($ticketId, 'ACCEPTED');
            return true;
        }
    }

    public function claimByLogin($ticketId)
    {
        $userId = $this->CI->the_auth->getLoginUserId();

        if ($this->CI->tickets_model->get(array('id' => $ticketId))) 
        {
            $find = $this->CI->tickets_staff_model->get(array(
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'is_read' => '1'
            ));
            if ($find) {
                $this->setError('ticket_lib_claim_already');
                return false;
            }

            $this->CI->load->model('staff/staff_model');
            $staff = $this->CI->staff_model->with('position', array('fields:pic_level'))->get(array('user_id' => $userId, 'position_id !=' => ''));
            if ($staff === FALSE) {
                $this->setError('ticket_lib_not_staff');
                return false;
            }

            $this->CI->db->trans_start();

            $this->CI->tickets_staff_model->insert(array(
                'ticket_id' => $ticketId,
                'user_id' => $userId
            ));
            $this->insertEvent($ticketId, TICKET_EVENT_CLAIM, 'user_id:' . $userId);

            $this->CI->tickets_model->update(array(
                'status' => TICKET_FLAG_PROGRESS,
                'pic_level' => $staff->position->pic_level
            ), array('id' => $ticketId));
            $this->insertEvent($ticketId, TICKET_EVENT_PROGRESS, 'user_id:' . $userId);

            $this->CI->db->trans_complete();
            if ($this->CI->db->trans_status() === FALSE) {
                $this->CI->db->trans_rollback();
                $this->setError('ticket_msg_claim_failed');
                return false;
            }

            $this->setMessage('ticket_lib_claim_success');
            return true;
        }
        else {
            $this->setError('lb_msg_request_failed');
            return false;
        }
    }

    public function requestCancellation($ticketId, $reasons = '')
    {
        $ticket = $this->CI->tickets_model->get(array('id' => $ticketId));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }
        $cancel_deleted = $this->CI->config->item('cancel_deleted');

        $this->CI->db->trans_start();

        if ($cancel_deleted) {
            $this->CI->tickets_model->delete(array('id' => $ticketId));
        }
        else {
            $this->CI->tickets_model->update([
                'flag' => TICKET_FLAG_CANCEL,
                'reason_cancel' => $reasons
            ], array('id' => $ticketId));

            $userId = $this->CI->the_auth_backend->getUserLoginId();
            $this->insertEvent($ticketId, TICKET_EVENT_CANCEL, 'user_id:' . $userId, null, $reasons);
        }

        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_msg_cancellation_failed');
            return false;
        }

        $this->setMessage('ticket_msg_cancellation_success');
        return true;
    }

    public function appendStaff($ticketId, $staffs, $userId = '', $transactionStart = TRUE)
    {
        if (empty($userId)) {
            $userId = $this->CI->the_auth_backend->getUserLoginId();
        }

        $ticket = $this->CI->tickets_model->get(array('id' => $ticketId));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        if (!is_array($staffs)) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $this->CI->load->model('staff/staff_model');

        $staff = array();
        $levels = array();
        foreach ($staffs as $item) {
            $get = $this->CI->staff_model
                ->with('pic_level')
                ->with('user')
                ->get(array('id' => $item));
            if ($get) {
                $claim = $this->CI->tickets_staff_model->count_rows(array('ticket_id' => $ticketId, 'staff_id' => $get->id));
                if ($claim <= 0) {
                    $staff[] = array(
                        'id' => $get->id,
                        'user_id' => $get->user_id,
                        'email' => $get->user->email,
                        'is_ready' => (bool) $get->is_ready
                    );
                    $levels[] = $get->pic_level->level;
                }
            }
        }
        asort($levels);
        $countLevels = count($levels);

        if (!$staff) {
            $this->setError('ticket_msg_append_staff_failed_already');
            return false;
        }

        if ($transactionStart) {
            $this->CI->db->trans_start();
        }

        $staffIds = array();
        $isReady = false;
        foreach ($staff as $val) {
            $this->CI->tickets_staff_model->insert(array(
                'ticket_id' => $ticketId,
                'staff_id' => $val['id'],
                'is_read' => '0',
            ));

            if ($val['is_ready']) {
                /*$this->CI->staff_model->update(array(
                    'is_ready' => '0'
                ), array('id' => $val['id']));*/
            }

            $staffIds[] = $val['user_id'];
            if ($val['is_ready'] && !$isReady) {
                $isReady = true;
            }
        }

        if ($staff) {
            $this->insertEvent($ticketId, TICKET_EVENT_ADD_STAFF, 'user_id:' . $userId, 'user_id:' . implode(',', $staffIds));

            $dataTicket = array(
                // 'pic_level' => $levels[$countLevels - 1],
                // 'flag' => $isReady ? TICKET_FLAG_PROGRESS : TICKET_FLAG_HOLD
                'flag' => TICKET_FLAG_HOLD
            );
            $this->CI->tickets_model->update($dataTicket, array('id' => $ticketId));
            // $this->insertEvent($ticketId, TICKET_EVENT_CHANGE_LEVEL, 'user_id:' . $userId, null, $levels[$countLevels - 1]);
            //if ($isReady) {
                // $this->insertEvent($ticketId, TICKET_EVENT_PROGRESS, 'user_id:' . $userId);
            // } else {
                //$this->insertEvent($ticketId, TICKET_EVENT_HOLD, 'user_id:' . $userId, 'user_id:' . implode(',', $staffIds) , TICKET_EVENT_WAIT_NEXT_LEVEL);
                $this->insertEvent($ticketId, TICKET_EVENT_HOLD, 'user_id:' . $userId, 'user_id:' . implode(',', $staffIds) , 'Waiting response from our staff');
            // }
        }

        if ($transactionStart) {
            $this->CI->db->trans_complete();
            if ($this->CI->db->trans_status() === false) {
                $this->CI->db->trans_rollback();
                $this->setError('ticket_msg_append_staff_failed');
                return false;
            }
        }

        $this->setMessage('ticket_msg_append_staff_success');
        return true;
    }

    public function removeStaff($ticketId, $staffId, $reason, $userId = '')
    {
        if (empty($userId)) {
            $userId = $this->CI->the_auth_backend->getUserLoginId();
        }

        $ticket = $this->CI->tickets_model->get(array('id' => $ticketId));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $findStaff = $this->CI->tickets_staff_model->count_rows(array('ticket_id' => $ticketId, 'staff_id' => $staffId));
        if ($findStaff <= 0) {
            $this->setError('ticket_msg_remove_staff_failed_not_found');
            return false;
        }

        $this->CI->db->trans_start();

        $this->CI->tickets_staff_model->delete(array('ticket_id' => $ticketId, 'staff_id' => $staffId));
        $this->insertEvent($ticketId, TICKET_EVENT_REMOVE_STAFF, 'user_id:' . $userId, 'user_id:' . $staffId, $reason);

        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_msg_remove_staff_failed');
            return false;
        }

        $this->setMessage('ticket_msg_remove_staff_success');
        return true;
    }

    public function delegationStaff($ticketId, $categorySubId, $reason, $userId = '', $transactionStart = true)
    {
        if (empty($userId)) {
            $userId = $this->CI->the_auth_backend->getUserLoginId();
        }

        $ticket = $this->CI->tickets_model->get(array('id' => $ticketId));
        if ($ticket === false) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        if (empty($categorySubId)) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        // get sub category information
        $this->CI->load->model('references/category_model');
        $subCategory = $this->CI->category_model
            ->with('staff')
            ->get(['id' => $categorySubId, 'active' => 'A']);
        if (!$subCategory) {
            $this->setError('ticket_lib_category_not_found');
            return false;
        }

        if ($transactionStart) {
            $this->CI->db->trans_start();
        }

        $this->CI->tickets_staff_model->delete(['ticket_id' => $ticketId]);

        $this->CI->tickets_model->update([
            'category_sub_id' => $subCategory->id,
            'estimate' => $subCategory->estimate,
            'priority_id' => $subCategory->auto_priority
        ], ['id' => $ticketId]);
        $this->insertEvent($ticketId, TICKET_EVENT_DELEGATION, 'end_user:' . $userId, null, $reason);
        $this->insertEvent($ticketId, TICKET_EVENT_CHANGE_CATEGORY, 'end_user:' . $userId);

        $autoSendStaff = (bool)$subCategory->auto_send_staff;
        if ($autoSendStaff) {
            $this->CI->load->model('staff/staff_view_model');
            if (isset($subCategory->staff) && count($subCategory->staff)) 
            {
                $countAddStaff = 0;
                foreach ($subCategory->staff as $staff) 
                {
                    $find = $this->CI->staff_view_model->get(['user_id' => $staff->user_id, 'active' => '1']);
                    if ($find) {
                        $countAddStaff += 1;
                        $this->CI->tickets_staff_model->insert([
                            'ticket_id' => $ticketId,
                            'staff_id' => $find->id,
                            'is_read' => '0'
                        ]);
                        $this->insertEvent($ticketId, TICKET_EVENT_ADD_STAFF, 'user_id:1', 'user_id:' . $find->user_id);
                    }
                }
            }
        }

        if ($transactionStart) {
            $this->CI->db->trans_complete();
            $this->CI->db->trans_complete();
            if ($this->CI->db->trans_status() === false) {
                $this->CI->db->trans_rollback();
                $this->setError('ticket_msg_append_staff_failed');
                return false;
            }
        }

        /*
        $this->CI->load->library('addons/the_email');
        $ticket = $this->CI->tickets_model
            ->with('staff')
            ->get(array('id' => $ticketId));
        if ($ticket) {
            if ($autoSendStaff) {
                if (isset($ticket->staff) && count($ticket->staff)) {
                    $this->CI->load->model('staff/staff_model');

                    $countAddStaff = 0;
                    foreach ($ticket->staff as $staff) {
                        $staff = $this->CI->staff_model
                            ->with('profile')
                            ->with('user')
                            ->get(['id' => $staff->staff_id]);
                        if ($staff) {
                            $countAddStaff += 1;
                            $dataEmailStaff = [
                                'slug'              => 'ticket_add_staff',
                                'ticket'            => $ticket,
                                'staff'             => $staff,
                                'to'                => $staff->user->email,
                                'tiket_date_send'   => date('d M Y - H:i:s', strtotime($ticket->created_at)),
                                'url_access_ticket' => site_url_backend('#queues/view/' . $ticket->id)
                            ];

                            $this->CI->the_email->send_email($dataEmailStaff);
                        }
                    }

                    if ($countAddStaff > 0) {
                        $this->changeStatus($ticketId, TICKET_FLAG_HOLD, '1', 'Waiting response from staff');
                    }
                }
            }
        }
        */

        $this->setMessage('ticket_msg_delegation_success');
        return true;
    }

    public function changeStatus($ticketId, $status, $userId = null, $reason = null, $cause = null, $solution = null, $keywords = [], $notifTelegram = false)
    {
        if (empty($userId)) {
            $userId = $this->CI->the_auth_backend->getUserLoginId();
        }
        
        $ticket = $this->CI->tickets_model
            ->get(array('id' => $ticketId));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        if ($ticket->flag === TICKET_FLAG_CLOSED) {
            $this->setError('ticket_msg_already_closed');
            return false;
        }

        if ($ticket->flag === $status) {
            $this->setError(sprintf('Ticket already has flag "%s"', $status), FALSE);
            return false;
        }

        if ($status == TICKET_FLAG_CLOSED) {
            return $this->closeTicket($ticketId, $reason, $cause, $solution, $keywords);
        }

        $this->CI->db->trans_start();

        $this->CI->tickets_model->update(array(
            'flag' => $status,
            'cause_descr' => $cause ? $cause : $ticket->cause_descr,
            'solution_descr' => $solution ? $solution : $ticket->solution_descr,
        ), array('id' => $ticketId));

        if ($status == TICKET_FLAG_HOLD) {
            $this->insertEvent($ticketId, TICKET_EVENT_HOLD, 'user_id:' . $userId, null, $reason);
        } else if ($status == TICKET_FLAG_PROGRESS) {
            $this->insertEvent($ticketId, TICKET_EVENT_PROGRESS, 'user_id:' . $userId, null, $reason);
        } else if ($status == TICKET_FLAG_FINISHED) {
            $this->insertEvent($ticketId, TICKET_EVENT_FINISH, 'user_id:' . $userId, null, $reason);
        } else if ($status == TICKET_FLAG_OPENED) {
            $this->insertEvent($ticketId, TICKET_EVENT_OPENED, 'user_id:' . $userId, null, $reason);
        }

        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_msg_change_status_failed');
            return false;
        }

        if ($notifTelegram) {
            // if ($status === TICKET_FLAG_PROGRESS) {
            //     $this->notifTelegramToEndUser($ticketId, 'PROGRESS', $reason);
            // }
            // else if ($status === TICKET_FLAG_HOLD) {
            //     $this->notifTelegramToEndUser($ticketId, 'HOLD', $reason);
            // }
             if ($status === TICKET_FLAG_FINISHED) {
                $this->notifTelegramToEndUser($ticketId, 'FINISHED', $reason);
             }
        }

        $this->setMessage('ticket_msg_change_status_success');
        return true;
    }

    public function closeTicket($ticketId, $reason = null, $cause = null, $solution = null, $keywords = [], $sendEmail = false)
    {
        $this->CI->load->model('staff/staff_model');

        $userId = $this->CI->the_auth_backend->getUserLoginId();
        $ticket = $this->CI->tickets_model
            ->get(array('id' => $ticketId));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $ticketStaff = $this->CI->tickets_staff_model
            ->get_all(array('ticket_id' => $ticketId));
        if ($ticketStaff === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $this->CI->load->library('references/keyword');

        $this->CI->db->trans_start();

        $this->CI->tickets_model->update(array(
            'flag' => TICKET_FLAG_CLOSED,
            // 'cause_descr' => $cause ? $cause : '',
            // 'solution_descr' => $solution ? $solution : '',
            'keywords' => Keyword::process($keywords)
        ), array('id' => $ticketId));

        if ($ticket->flag !== TICKET_FLAG_CLOSED) {
            $this->insertEvent($ticketId, TICKET_EVENT_CLOSED, 'user_id:' . $userId, null, $reason);
        }

        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_msg_close_failed');
            return false;
        }

        $this->setMessage('ticket_msg_close_success');
        return true;
    }

    public function returnTicket($ticketId, $reason = '')
    {
        return $this->CI->tickets_model->returnIt($ticketId, $reason);
    }

    public function finishTicket($ticketId, $reason = null, $sendEmail = false)
    {
        $this->CI->load->model('staff/staff_model');

        $userId = $this->CI->the_auth->getLoginUserId();
        $ticket = $this->CI->ticket_model
            ->get(array('id' => $ticketId));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $ticketStaff = $this->CI->tickets_staff_model
            ->get_all(array('ticket_id' => $ticketId));
        if ($ticket === FALSE) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $this->CI->db->trans_start();

        $this->CI->ticket_model->update(array('status' => TICKET_FLAG_FINISHED), array('id' => $ticketId));
        $this->insertEvent($ticketId, TICKET_EVENT_FINISH, 'user_id:' . $userId, null, $reason);

        // ambil data ticket dari tiap staff
        foreach ($ticketStaff as $staff)
        {
            $get = $this->CI->tickets_staff_model
                ->fields('user_id,ticket_id')
                ->get_all(array('user_id' => $staff->user_id, 'ticket_id !=' => $ticketId));

            // jika staff sudah tdk memiliki tiket
            if ($get === FALSE) {
                $this->CI->staff_model->update(array('is_ready' => 1), array('user_id' => $staff->user_id));
                continue;
            }

            // ambil tiket yang sementara progress/hold dari staff
            $ticketArr = array();
            foreach ($get as $item) {
                $ticketArr[] = $item->ticket_id;
            }
            unset($get);

            $ticketCount = $this->CI->ticket_model
                ->fields('id,status')
                ->where('id', 'in', $ticketArr)
                ->where('status', 'in', array(TICKET_FLAG_PROGRESS, TICKET_FLAG_HOLD))
                ->count_rows();
            if ($ticketCount >= 1) {
                $this->CI->staff_model->update(array('is_ready' => 1), array('user_id' => $staff->user_id));
            }
        }

        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            $this->setError('ticket_msg_finish_failed');
            return false;
        }

        $this->setMessage('ticket_msg_finish_success');
        return true;
    }

    public function countStatusTicketByDay($year, $month)
    {
        $this->CI->db->reset_query();

        $excludeCategory = Setting::get('dash_ticket_countdown_hide', 'tickets');

        // ticket created by day
        $ticketCreated = $this->CI->tickets_model
            ->where('category_id', [$excludeCategory], NULL, FALSE, TRUE)
            ->count_rows(array('YEAR(created_at)' => $year, 'MONTH(created_at)' => $month));

        // ticket progress by day
        $ticketProgress = $this->CI->tickets_model
            ->where('category_id', [$excludeCategory], NULL, FALSE, TRUE)
            ->count_rows(array('YEAR(created_at)' => $year, 'MONTH(created_at)' => $month, 'flag' => TICKET_FLAG_PROGRESS));

        // ticket hold by day
        $ticketHold = $this->CI->tickets_model
            ->where('category_id', [$excludeCategory], NULL, FALSE, TRUE)
            ->count_rows(array('YEAR(created_at)' => $year, 'MONTH(created_at)' => $month, 'flag' => TICKET_FLAG_HOLD));

        // ticket finish by day
        $ticketFinish = $this->CI->tickets_model
            ->where('category_id', [$excludeCategory], NULL, FALSE, TRUE)
            ->count_rows(array('YEAR(created_at)' => $year, 'MONTH(created_at)' => $month, 'flag' => TICKET_FLAG_FINISHED));

        // ticket close by day
        $ticketClose = $this->CI->tickets_model
            ->where('category_id', [$excludeCategory], NULL, FALSE, TRUE)
            ->count_rows(array('YEAR(created_at)' => $year, 'MONTH(created_at)' => $month, 'flag' => TICKET_FLAG_CLOSED));

        return array(
            'ticket_created' => $ticketCreated,
            'ticket_progress' => $ticketProgress,
            'ticket_hold' => $ticketHold,
            'ticket_finish' => $ticketFinish,
            'ticket_close' => $ticketClose,
        );
    }

    public function requestReports($data)
    {
        require_once APPPATH . '/third_party/Carbon-1.32.0/autoload.php';

        if (!array_key_exists('dateStart', $data) && !array_key_exists('dateEnd', $data) && 
            !array_key_exists('category', $data) && !array_key_exists('categorySub', $data) &&
            !array_key_exists('sbu', $data) && !array_key_exists('staff', $data) &&
            !array_key_exists('flag', $data)) {
            $this->setError('ticket_lib_request_failed');
            return false;
        }

        $this->CI->load->model('tickets/tickets_view_model');
        $this->CI->load->model('tickets/tickets_staff_model');
        $this->CI->load->model('tickets/tickets_staff_view_model');
        $this->CI->load->model('tickets/tickets_note_model');

        $startDate = date_create($data['dateStart']);
        $startY = date_format($startDate, 'Y');
        $startM = date_format($startDate, 'm');
        $startD = date_format($startDate, 'd');
        $endDate = date_create($data['dateEnd']);
        $endY = date_format($endDate, 'Y');
        $endM = date_format($endDate, 'm');
        $endD = date_format($endDate, 'd');
        $startDateku = date_format($startDate, 'Y-m-d');
        $endDateku = date_format($endDate, 'Y-m-d');

        // posting tiket yng blum di tutup atau di nyatakan finish
        $willPosts = $this->CI->db->select('id')
            ->from('tickets')
            
            ->where_not_in('flag', [TICKET_FLAG_FINISHED, TICKET_FLAG_CLOSED])
            ->where('created_at >=', $startDateku)
            ->where('created_at <=', $endDateku)
            ->get();
        $willPostResults = $willPosts->result();
        if ($willPostResults) {
            foreach ($willPostResults as $willPostResult) {
                $this->ticketPosting($willPostResult->id);
            }
        }

        $this->CI->db->select('ticket.*')
            ->from($this->CI->tickets_view_model->table . ' ticket')
            
            ->where('ticket.created_at >=', $startDateku)
            ->where('ticket.created_at <=', $endDateku)
            ->group_by('ticket.id');

        if ($data['flag'] !== 'ALL') {
            $this->CI->db->group_start()
                ->where_in('ticket.flag', $data['flag'])
                ->group_end();
        }

        if ($data['category'] !== 'ALL') {
            $this->CI->db->group_start()
                ->where('ticket.services_id', $data['category'])
                ->group_end();
        }
        if ($data['category'] === 'ALL') {
            $excludeCategory = Setting::get('dash_ticket_countdown_hide', 'tickets');
            $this->CI->db->group_start()
                ->where_not_in('ticket.services_id', $excludeCategory)
                ->group_end();
        }

        if ($data['categorySub'] !== 'ALL') {
            $this->CI->db->group_start()
                ->where('ticket.category_id', $data['categorySub'])
                ->group_end();
        }

        if ($data['sbu'] !== 'ALL') {
            $this->CI->db->group_start()
                ->where('ticket.company_id', $data['sbu'])
                ->group_end();
        }

        if ($data['staff'] !== 'ALL') {
            $this->CI->db->join(
                $this->CI->tickets_staff_model->table . ' staff', 
                'ticket.id = staff.ticket_id',
                'LEFT'
            );
            $this->CI->db->group_start()
                ->where('staff.staff_id', $data['staff'])
                ->where('staff.is_claimed', '1')
                ->group_end();
        }

        $query = $this->CI->db->get();
        //echo $this->CI->db->last_query();die;
        $allTickets = $query->result();
        
        $tickets = [];
        $ticketsGrouped = [];
        if ($allTickets)
        {
            foreach ($allTickets as $ticket)
            {
                if (!in_array($ticket->flag, [TICKET_FLAG_REQUESTED, TICKET_FLAG_CANCEL, TICKET_FLAG_CLOSED])) {
                    $this->ticketPosting($ticket->id);
                    $ticket = $this->CI->db->select('ticket.*')
                        ->from($this->CI->tickets_view_model->table . ' ticket')
                        ->where('ticket.id', $ticket->id)
                        ->group_by('ticket.id')
                        ->get()->row();
                }

                $allStaff = $this->CI->tickets_staff_view_model->get_all([
                    'ticket_id' => $ticket->id,
                    'is_claimed' => '1'
                ]);
                $staff = [];
                if ($allStaff) {
                    foreach ($allStaff as $row) {
                        $staff[] = $row->full_name;
                    }
                }
                
                $allNotes = $this->CI->tickets_note_model->get_all([
                    'ticket_id' => $ticket->id,
                ]);
                $notes = [];
                if ($allNotes) {
                    foreach ($allNotes as $note) {
                        $notes[] = $note->description;
                    }
                }
                $now = new \Carbon\Carbon();

                $workDuration = $ticket->duration_work;
                $holdDuration = $ticket->duration_hold;
                $leadTimeDuration = $workDuration - $holdDuration;
//                $isAchieveSla = '0';
//                if ($ticket->flag === TICKET_FLAG_FINISHED) {
                $isAchieveSla = $leadTimeDuration > $ticket->estimate ? '0' : '1';
//                }

                $ticketDuration = $ticket->duration;
                if (!$ticket->date_closed && !$ticketDuration) {
                    $requestedDate = new \Carbon\Carbon($ticket->created_at);
                    $ticketDuration = $requestedDate->diffInSeconds($now);
                }

                $tickets[] = [
                    'no'                => $ticket->number,
                    'subject'           => $ticket->subject,
                    'description'       => $ticket->description,
                    'informer_name'     => $ticket->informer_full_name,
                    'company'           => $ticket->company_name,
                    'company_branch'    => $ticket->company_branch_name,
                    'status'            => $ticket->flag,
                    'category_head'     => $ticket->category_name,
                    'category_sub'     => $ticket->category_sub_name,
                    'staff_name'        => implode(', ', $staff),
                    'sla'               => $ticket->estimate > 0 ? $ticket->estimate / 3600 : -1,
                    'date_open'         => $ticket->date_opened,
                    'date_close'        => $ticket->date_closed,
                    'duration'          => timespanFormat(0, $ticketDuration),
                    'duration_hold'     => timespanFormat(0, $ticket->duration_hold),
                    'duration_work'     => timespanFormat(0, $ticket->duration_work),
                    //'duration_leadtime' => timespanFormat(0, $leadTimeDuration),
                    'duration_leadtime' => $leadTimeDuration > 0 ? $leadTimeDuration : 0,
                    'response_helpdesk' => timespanFormat(0, $ticket->response_helpdesk),
                    'response_pic'      => timespanFormat(0, $ticket->response_pic),
                    'is_achieve_sla'    => $ticket->estimate > 0 ? $isAchieveSla : '1',
                    'notes'             => count($notes) > 0 ? '- ' . implode("\n -", $notes) : '',
                ];
            }

            foreach ($tickets as $ticket) {
                $ticketsGrouped[$ticket['company']][] = $ticket;
            }
        }

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load(APPPATH . '/modules/reports/template/reports_sla.xlsx');
        $spreadsheet->getProperties()
            ->setCreator('Macca system')
            ->setLastModifiedBy('Macca system')
            ->setTitle(sprintf('Reports macca system %s-%s', $data['dateStart'], $data['dateEnd']))
            ->setSubject('Report')
            ->setKeywords('Macca, Report');

        try {
            foreach ($ticketsGrouped as $key => $item) {
                $clonedWorksheet = $spreadsheet->getSheetByName('ALL SBU')->copy();
                $clonedWorksheet->setTitle($key ? $key : 'UNDEFINED');
                $spreadsheet->addSheet($clonedWorksheet);
            }

            $this->requestReportSheet($spreadsheet, 'ALL SBU', $tickets);
            foreach ($ticketsGrouped as $key => $item) {
                $this->requestReportSheet($spreadsheet, $key, $item);
            }

            $spreadsheet->setActiveSheetIndexByName('ALL SBU');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $fileName = sprintf('Report_macca_[%s].xlsx', now());
            $writer->save(APPPATH . 'cache/files/' . $fileName);

            $this->setMessage('Generate report success', FALSE);
            return $fileName;

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            log_message('ERROR', $e->getMessage());
            return false;
        }
    }

    /**
     * Generate sheet from requestReport method
     *
     * @param PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param string $sheetName
     * @param array $tickets
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function requestReportSheet($spreadsheet, $sheetName, $tickets)
    {
        $spreadsheet->setActiveSheetIndexByName($sheetName);
        $sheet = $spreadsheet->getActiveSheet();
        $styleThickBrownBorderOutline = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];

        $rowInc = 3;
        $countOpened = 0;
        $countClosed = 0;
        $countAchieve = 0;
        $countAchieveNot = 0;
        foreach ($tickets as $ticket)
        {
            $sheet->setCellValue('A' . $rowInc, $ticket['no']);
            $sheet->setCellValue('B' . $rowInc, $ticket['subject']);
            $sheet->setCellValue('C' . $rowInc, $ticket['description']);
            $sheet->setCellValue('D' . $rowInc, $ticket['company']);
            $sheet->setCellValue('E' . $rowInc, $ticket['company_branch']);
            $sheet->setCellValue('F' . $rowInc, $ticket['informer_name']);
            $sheet->setCellValue('G' . $rowInc, $ticket['date_open']);
            $sheet->setCellValue('H' . $rowInc, $ticket['date_close']);
            $sheet->setCellValue('I' . $rowInc, $ticket['status']);
            $sheet->setCellValue('J' . $rowInc, $ticket['category_head']);
            $sheet->setCellValue('K' . $rowInc, $ticket['category_sub']);
            $sheet->setCellValue('L' . $rowInc, $ticket['staff_name']);
            $sheet->setCellValue('M' . $rowInc, $ticket['sla']);
            $sheet->setCellValue('N' . $rowInc, $ticket['duration']);
            $sheet->setCellValue('O' . $rowInc, $ticket['response_helpdesk']);
            $sheet->setCellValue('P' . $rowInc, $ticket['response_pic']);
            $sheet->setCellValue('Q' . $rowInc, $ticket['duration_work']);
            $sheet->setCellValue('R' . $rowInc, $ticket['duration_hold']);
            //$sheet->setCellValue('S' . $rowInc, $ticket['duration_leadtime']);
            $sheet->setCellValue('S' . $rowInc, '=' . $ticket['duration_leadtime'] . '/86400');
            $sheet->getStyle('S' . $rowInc)->getNumberFormat()->setFormatCode('dd:hh:mm:ss');
            $sheet->setCellValue('T' . $rowInc, $ticket['is_achieve_sla']);
            $sheet->setCellValue('U' . $rowInc, $ticket['notes']);
            $sheet->getStyle('U' . $rowInc)->getAlignment()->setWrapText(true);

            $sheet->getStyle('A' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('B' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('C' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('D' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('E' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('F' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('G' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('H' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('I' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('J' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('K' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('L' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('M' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('N' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('O' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('P' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('Q' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('R' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('S' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('T' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getStyle('U' . $rowInc)->applyFromArray($styleThickBrownBorderOutline);
            $sheet->getRowDimension($rowInc)->setRowHeight(30);

            if (!empty($ticket['date_open'])) {
                $countOpened++;
            }
            if ($ticket['status'] === TICKET_FLAG_CLOSED || $ticket['status'] === TICKET_FLAG_FINISHED) {
                $countClosed++;
            }
            $flag = [TICKET_FLAG_REQUESTED, TICKET_FLAG_CANCEL];
            if ($ticket['is_achieve_sla'] === '1' && !in_array($ticket['status'], $flag)) {
                $countAchieve++;
            }
            if ($ticket['is_achieve_sla'] === '0') {
                $countAchieveNot++;
            }

            $rowInc++;
        }

        $rowInc += 2;
        $sheet->setCellValue('C' . $rowInc, 'SUMMARY REPORT');
        $sheet->getStyle('C' . $rowInc)->getFont()->setBold(true);
        $sheet->getRowDimension($rowInc)->setRowHeight(30);

        $rowIncCreated = $rowInc + 1;
        $sheet->setCellValue('C' . $rowIncCreated, 'Ticket created');
        $sheet->setCellValue('D' . $rowIncCreated, count($tickets));
        $sheet->getStyle('C' . $rowIncCreated)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getStyle('D' . $rowIncCreated)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getRowDimension($rowIncCreated)->setRowHeight(30);

        $rowIncOpened = $rowIncCreated + 1;
        $sheet->setCellValue('C' . $rowIncOpened, 'Ticket opened');
        $sheet->setCellValue('D' . $rowIncOpened, $countOpened);
        $sheet->getStyle('C' . $rowIncOpened)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getStyle('D' . $rowIncOpened)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getRowDimension($rowIncOpened)->setRowHeight(30);

        $rowIncClosed = $rowIncOpened + 1;
        $sheet->setCellValue('C' . $rowIncClosed, 'Ticket closed');
        $sheet->setCellValue('D' . $rowIncClosed, $countClosed);
        $sheet->getStyle('C' . $rowIncClosed)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getStyle('D' . $rowIncClosed)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getRowDimension($rowIncClosed)->setRowHeight(30);

        $rowIncAchieve = $rowIncClosed + 1;
        $sheet->setCellValue('C' . $rowIncAchieve, 'Achieve SLA');
        $sheet->setCellValue('D' . $rowIncAchieve, $countAchieve);
        $sheet->setCellValue('E' . $rowIncAchieve, "=(D$rowIncAchieve / D$rowIncClosed) * 100%");
        $sheet->getStyle('C' . $rowIncAchieve)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getStyle('D' . $rowIncAchieve)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getStyle('E' . $rowIncAchieve)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getRowDimension($rowIncAchieve)->setRowHeight(30);

        $rowIncAchieveNot = $rowIncAchieve + 1;
        $sheet->setCellValue('C' . $rowIncAchieveNot, 'Achieve SLA (not)');
        $sheet->setCellValue('D' . $rowIncAchieveNot, $countAchieveNot);
        $sheet->setCellValue('E' . $rowIncAchieveNot, "=(D$rowIncAchieveNot / D$rowIncClosed) * 100%");
        $sheet->getStyle('C' . $rowIncAchieveNot)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getStyle('D' . $rowIncAchieveNot)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getStyle('E' . $rowIncAchieveNot)->applyFromArray($styleThickBrownBorderOutline);
        $sheet->getRowDimension($rowIncAchieveNot)->setRowHeight(30);

        $xAxisTickValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING,
                'Reports!$C$'.$rowIncCreated.':$C$'.$rowIncAchieveNot,
                null,
                5
            )
        ];
        $dataSeriesValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER,
                'Reports!$D$'.$rowIncCreated.':$D$'.$rowIncAchieveNot,
                null,
                5
            ),
        ];
        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART, // plotType
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STANDARD, // plotGrouping
            range(0, count($dataSeriesValues) - 1), // plotOrder
            [], // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues        // plotValues
        );
        $series->setPlotDirection(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::DIRECTION_COL);

        $plotArea = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
        $legend = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, null, false);
        $title = new \PhpOffice\PhpSpreadsheet\Chart\Title('Tickets');
        $yAxisLabel = new \PhpOffice\PhpSpreadsheet\Chart\Title('Value');
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            'tickets', // name
            $title, // title
            $legend, // legend
            $plotArea, // plotArea
            true, // plotVisibleOnly
            0, // displayBlanksAs
            null, // xAxisLabel
            $yAxisLabel  // yAxisLabel
        );

        $rowChart = $rowIncAchieveNot + 2;
        $rowChartEnd = $rowIncAchieveNot + 10;
        $chart->setTopLeftPosition('C'.$rowChart);
        $chart->setBottomRightPosition('E'.$rowChartEnd);
        $sheet->addChart($chart);
    }

    public function ticketCountMonthly()
    {
        $this->CI->db->select([
            'count( IF ( ( MONTH(date_opened) = 1 ), date_opened, NULL ) ) AS month_1',
            'count( IF ( ( MONTH(date_opened) = 2 ), date_opened, NULL ) ) AS month_2',
            'count( IF ( ( MONTH(date_opened) = 3 ), date_opened, NULL ) ) AS month_3',
            'count( IF ( ( MONTH(date_opened) = 4 ), date_opened, NULL ) ) AS month_4',
            'count( IF ( ( MONTH(date_opened) = 5 ), date_opened, NULL ) ) AS month_5',
            'count( IF ( ( MONTH(date_opened) = 6 ), date_opened, NULL ) ) AS month_6',
            'count( IF ( ( MONTH(date_opened) = 7 ), date_opened, NULL ) ) AS month_7',
            'count( IF ( ( MONTH(date_opened) = 8 ), date_opened, NULL ) ) AS month_8',
            'count( IF ( ( MONTH(date_opened) = 9 ), date_opened, NULL ) ) AS month_9',
            'count( IF ( ( MONTH(date_opened) = 10 ), date_opened, NULL ) ) AS month_10',
            'count( IF ( ( MONTH(date_opened) = 11 ), date_opened, NULL ) ) AS month_11',
            'count( IF ( ( MONTH(date_opened) = 12 ), date_opened, NULL ) ) AS month_12' 
        ]);
        $year = date('Y');
        $this->CI->db->where('YEAR(created_at)', $year);
        $queryOpened = $this->CI->db->get('tickets')->row_array();

        $this->CI->db->select([
            'count( IF ( ( MONTH(date_closed) = 1 ), date_closed, NULL ) ) AS month_1',
            'count( IF ( ( MONTH(date_closed) = 2 ), date_closed, NULL ) ) AS month_2',
            'count( IF ( ( MONTH(date_closed) = 3 ), date_closed, NULL ) ) AS month_3',
            'count( IF ( ( MONTH(date_closed) = 4 ), date_closed, NULL ) ) AS month_4',
            'count( IF ( ( MONTH(date_closed) = 5 ), date_closed, NULL ) ) AS month_5',
            'count( IF ( ( MONTH(date_closed) = 6 ), date_closed, NULL ) ) AS month_6',
            'count( IF ( ( MONTH(date_closed) = 7 ), date_closed, NULL ) ) AS month_7',
            'count( IF ( ( MONTH(date_closed) = 8 ), date_closed, NULL ) ) AS month_8',
            'count( IF ( ( MONTH(date_closed) = 9 ), date_closed, NULL ) ) AS month_9',
            'count( IF ( ( MONTH(date_closed) = 10 ), date_closed, NULL ) ) AS month_10',
            'count( IF ( ( MONTH(date_closed) = 11 ), date_closed, NULL ) ) AS month_11',
            'count( IF ( ( MONTH(date_closed) = 12 ), date_closed, NULL ) ) AS month_12' 
        ]);
        $year = date('Y');
        $this->CI->db->where('YEAR(created_at)', $year);
        $queryClosed = $this->CI->db->get('tickets')->row_array();

        return [
            'year' => $year,
            'data' => [
                'opened' => $queryOpened,
                'closed' => $queryClosed
            ]
        ];
    }

    public function ticketCountSBU($year, $month)
    {
        $this->CI->db->select([
            'company.abbr',
            sprintf(
                '(SELECT COUNT(ticket.id) FROM uf_tickets ticket WHERE ticket.company_id = company.id AND YEAR(ticket.date_opened) = %s AND MONTH(ticket.date_opened) = %s) AS cnt_request',
                $year, $month
            ),
            sprintf(
                '(SELECT COUNT(ticket.id) FROM uf_tickets ticket WHERE ticket.company_id = company.id AND YEAR(ticket.date_closed) = %s AND MONTH(ticket.date_closed) = %s AND ticket.flag = "%s") AS cnt_closed',
                $year, $month, TICKET_FLAG_CLOSED
            )
        ]);
        $this->CI->db->from('uf_ref_companies company');
        $this->CI->db->where('company.active', 'A');
        $this->CI->db->order_by('company.abbr', 'ASC');
        $query = $this->CI->db->get();
        return [
            'year'  => $year,
            'month' => $month,
            'rows'  => $query->result_array()
        ];
    }

    public function ticketCountCategory($year, $month)
    {
        $this->CI->db->select([
            'category.id',
            'category.parent_id',
            'category.name',
            sprintf(
                '(SELECT COUNT(ticket.id) FROM uf_tickets ticket WHERE ticket.category_id = category.id AND YEAR(ticket.date_opened) = %s AND MONTH(ticket.date_opened) = %s) AS cnt_head',
                $year, $month
            ),
            sprintf(
                '(SELECT COUNT(ticket.id) FROM uf_tickets ticket WHERE ticket.category_sub_id = category.id AND YEAR(ticket.date_opened) = %s AND MONTH(ticket.date_opened) = %s) AS cnt_child',
                $year, $month
            ),
        ]);
        $this->CI->db->from('uf_ref_categories category');
        $this->CI->db->where('category.active', 'A');
        $query = $this->CI->db->get();
        return [
            'year'  => $year,
            'month' => $month,
            'rows'  => $query->result_array()
        ];
    }

    public function ticketCountStaff($year, $month)
    {
        $this->CI->db->select(['id','user_id','parent_id','full_name']);
        $this->CI->db->where('active', '1');
        $this->CI->db->order_by('full_name', 'ASC');
        $quStaff = $this->CI->db->get('uf_staff_view');
        $resStaff = $quStaff->result_array();
        
        foreach ($resStaff as &$staff) 
        {
            $this->CI->db->from('uf_tickets_staff staff');
            $this->CI->db->join('uf_tickets ticket', 'staff.ticket_id=ticket.id', 'LEFT');
            $this->CI->db->where('staff.staff_id', $staff['id']);
            $this->CI->db->where('YEAR(ticket.date_opened)', $year);
            $this->CI->db->where('MONTH(ticket.date_opened)', $month);
            $count = $this->CI->db->count_all_results();
            $staff['cnt'] = $count;
        }

        return [
            'year'  => $year,
            'month' => $month,
            'rows'  => $resStaff
        ];
    }

    public function ticketDurationWork($logEvents, $untilNow = FALSE) 
    {
        if ($logEvents) 
        {
            // begin get duration working time
            $filterAccept = array_filter($logEvents, function($log) {
                $log = (array) $log;
                return $log['event'] === TICKET_EVENT_ACCEPT;
            });
            $filterAccept = array_values($filterAccept);
            if (!$filterAccept) {
                return null;
            }
            
            $filterAccept = $filterAccept[0];
            $filterAccept = is_object($filterAccept) 
                ? $filterAccept->event_date
                : $filterAccept['event_date'];
            
            if (!$untilNow) {
                $filterFinished = array_filter($logEvents, function($log) {
                    $log = (array) $log;
                    return $log['event'] === TICKET_EVENT_FINISH;
                });
                $filterFinished = array_values($filterFinished);
                $filterFinished = end($filterFinished);
                $filterFinished = is_object($filterFinished) 
                    ? $filterFinished->event_date
                    : $filterFinished['event_date'];
            }
            else {
                $filterFinished = date('Y-m-d H:i:s');
            }
            
            $durationWorkingTime = strtotime($filterFinished) - strtotime($filterAccept);
            return $durationWorkingTime;
        }

        return null;
    }

    public function ticketDurationHold($logEvents) 
    {
        if ($logEvents) 
        {
            // begin get duration working time
            $filterAccept = array_filter($logEvents, function($log) {
                $log = (array) $log;
                return $log['event'] === TICKET_EVENT_ACCEPT;
            });
            $filterAccept = array_values($filterAccept);
            if (!$filterAccept) {
                return null;
            }
            $filterAccept = $filterAccept[0];

            $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
                $row = (array) $row;

                if (is_object($filterAccept)) {
                    return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept->id;
                }
                else {
                    return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept['id'];
                }
            });

            $intervalHold = 0;
            foreach ($filterEventHold as $eventHold) {
                $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
                    $rowNext = (array) $rowNext;

                    if (is_object($eventHold)) {
                        return $rowNext['event_from_id'] === $eventHold->id;
                    }
                    else {
                        return $rowNext['event_from_id'] === $eventHold['id'];
                    }
                });
                $nextEvent = array_values($nextEvent);

                $eventHoldStart = is_object($eventHold) 
                    ? new DateTime($eventHold->event_date)
                    : new DateTime($eventHold['event_date']);
                $eventHoldEnd = is_object($nextEvent[0]) 
                    ? new DateTime($nextEvent[0]->event_date)
                    : new DateTime($nextEvent[0]['event_date']);

                $interval = $eventHoldEnd->getTimestamp() - $eventHoldStart->getTimestamp();
                $intervalHold = $intervalHold + $interval;
            }
            return $intervalHold;
        }

        return null;
    }

    public function ticketTickCountDown($year = null)
    {
        require_once APPPATH . '/third_party/Carbon-1.32.0/autoload.php';

        if (empty($year)) {
            $year = date('Y');
        }

        $excludeCategory = Setting::get('dash_ticket_countdown_hide', 'tickets');

        // ticket progress by day
        $ticketProgress = $this->CI->tickets_model
            ->as_array()
            ->fields('id,uid,number,subject,description,flag,is_read,estimate,date_opened')
//            ->where(['YEAR(date_opened)' => $year], NULL, NULL, FALSE, FALSE, TRUE)
            ->where('flag', TICKET_FLAG_PROGRESS)
            ->where('category_id', [$excludeCategory], NULL, FALSE, TRUE)
            ->with('logs',['fields:id,ticket_id,event,event_by,event_by_ref_table,event_to,event_to_ref_table,event_from_id,reason,event_date'])
            ->with('staff_v',['fields:id,ticket_id,staff_id,is_read,created_at,user_id,full_name,nik,email,is_claimed'])
            ->with('notes', ['order_inside:id desc'])
            ->get_all();
        
        $tickets = [];
        $countProgress = 0;
        $countHold = 0;
        if ($ticketProgress)
        {
            $progressCount = 0;
            foreach ($ticketProgress as &$ticket) 
            {
                $logEvents = $ticket['logs'];
                $filterAccept = array_filter($logEvents, function($log) {
                    return $log->event === TICKET_EVENT_ACCEPT;
                });
                if ($filterAccept) 
                {
                    $filterAccept = array_values($filterAccept);
                    $filterAccept = $filterAccept[0];
                    $filterAcceptDate =  new \Carbon\Carbon($filterAccept->event_date);
                    $currentDate = new \Carbon\Carbon();
                    $durationWorkingTime = $filterAcceptDate->diffInSeconds($currentDate);

                    $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
                        return $row->event === TICKET_EVENT_HOLD && $row->id > $filterAccept->id;
                    });
                    $intervalHold = 0;
                    foreach ($filterEventHold as $eventHold) {
                        $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
                            return $rowNext->event === TICKET_EVENT_PROGRESS && $rowNext->id > $eventHold->id;
                        });
                        if (count($nextEvent) <= 0) {
                            $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
                                return $rowNext->event_from_id === $eventHold->id;
                            });
                        }

                        $nextEvent = array_values($nextEvent);
                        $nextEvent = $nextEvent[0];

                        $eventStart = new \Carbon\Carbon($eventHold->event_date);
                        $eventEnd = new \Carbon\Carbon($nextEvent->event_date);
                        $diffInSeconds = $eventStart->diffInSeconds($eventEnd);
                        $intervalHold = $intervalHold + $diffInSeconds;
                    }

                    $ticket['longtime_ticket'] = $durationWorkingTime;
                    $ticket['interval_hold'] = $intervalHold;
                    $ticket['working_time'] = $durationWorkingTime - $intervalHold;

                    $workingTimeProgress = intval($ticket['estimate']) - ($ticket['working_time']);
                    $ticket['working_progress'] = $workingTimeProgress;

                    $tickets[] = $ticket;
                    $progressCount++;
                }
            }
            $countProgress = $progressCount;
        }

        if ($tickets) {
            usort($tickets, function($a, $b) {
                if (strtotime($a['working_progress']) < strtotime($b['working_progress']))
                    return -1;
                else if (strtotime($a['working_progress']) > strtotime($b['working_progress']))
                    return 1;
                else
                    return 0;
            });
        }

        $ticketHolds = $this->CI->tickets_model
            ->as_array()
            ->fields('id,uid,number,subject,description,flag,is_read,estimate,date_opened')
            ->where('flag', TICKET_FLAG_HOLD)
            ->where('category_id', [$excludeCategory], NULL, FALSE, TRUE)
            ->with('logs',['fields:id,ticket_id,event,event_by,event_by_ref_table,event_to,event_to_ref_table,event_from_id,reason,event_date'])
            ->with('staff_v',['fields:id,ticket_id,staff_id,is_read,created_at,user_id,full_name,nik,email,is_claimed'])
            ->with('notes', ['order_inside:id desc'])
            ->get_all();
        if ($ticketHolds) {
            $holdCount = 0;
            foreach ($ticketHolds as &$ticketHold) {
                $logEvents = $ticketHold['logs'];
                $filterAccept = array_filter($logEvents, function ($log) {
                    return $log->event === TICKET_EVENT_ACCEPT;
                });
                if ($filterAccept) {

                    $ticketHold['longtime_ticket'] = 0;
                    $ticketHold['interval_hold'] = 0;
                    $ticketHold['working_time'] = 0;

                    $ticketHold['working_progress'] = 0;

                    $tickets[] = $ticketHold;
                    $holdCount++;
                }
            }
            $countHold = $holdCount;
        }

        return [
            'rows' => $tickets,
            'count_progress' => $countProgress,
            'count_hold' => $countHold
        ];
    }

    public function ticketPosting_old($id)
    {
        $this->CI->load->model('tickets/tickets_model');
        $this->CI->load->model('tickets/tickets_log_model');

        $ticket = $this->CI->tickets_model->get(['id' => $id]);
        if ($ticket)
        {
            $logEvents = $this->CI->tickets_log_model
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

                // if date opened empty
                if (empty($ticket->date_opened)) 
                {
                    $this->CI->tickets_model->update([
                        'date_opened' => $dateOpened,
                    ], ['id' => $ticket->id]);
                }

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

                // if date opened empty
                if (empty($ticket->response_pic)) {
                    $this->CI->tickets_model->update([
                        'response_pic' => $intervalPicResponse,
                    ], ['id' => $ticket->id]);
                }

                if ($ticket->flag === TICKET_FLAG_FINISHED)
                {
                    // begin get duration working time
                    $filterAccept = array_filter($logEvents, function($log) {
                        return $log['event'] === TICKET_EVENT_ACCEPT;
                    });
                    $filterAccept = array_values($filterAccept);
                    $filterAccept = $filterAccept[0];

                    $filterFinished = array_filter($logEvents, function($log) {
                        return $log['event'] === TICKET_EVENT_FINISH;
                    });
                    $filterFinished = array_values($filterFinished);
                    $filterFinished = end($filterFinished);

                    $durationWorkingTime = strtotime($filterFinished['event_date']) - strtotime($filterAccept['event_date']);
                    // end get duration working time

                    // begin get duration hold time
                    $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
                        return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept['id'];
                    });

                    $intervalHold = 0;
                    foreach ($filterEventHold as $eventHold) {
                        $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold) {
                            return $rowNext['event_from_id'] === $eventHold['id'];
                        });
                        $nextEvent = array_values($nextEvent);

                        $eventHoldStart = new DateTime($eventHold['event_date']);
                        $eventHoldEnd = new DateTime($nextEvent[0]['event_date']);
                        $interval = $eventHoldEnd->getTimestamp() - $eventHoldStart->getTimestamp();
                        $intervalHold = $intervalHold + $interval;
                    }
                    // end get duration hold time

                    $this->CI->tickets_model->update([
                        'duration_work' => $durationWorkingTime,
                        'duration_hold' => $intervalHold,
                    ], ['id' => $ticket->id]);
                }

                if ($ticket->flag === TICKET_FLAG_CLOSED && !empty($dateClosed))
                {
                    // get duration ticket (request - closed)
                    $dateTimeRequested = new DateTime($dateRequested);
                    $dateTimeClosed = new DateTime($dateClosed);
                    $intervalRequestClosed = $dateTimeClosed->getTimestamp() - $dateTimeRequested->getTimestamp();

                    $this->CI->tickets_model->update([
                        'duration' => $intervalRequestClosed,
                        'date_closed' => $dateClosed,
                    ], ['id' => $ticket->id]);
                }
            }
        }
    }

    public function ticketPosting($id)
    {
        require_once APPPATH . '/third_party/Carbon-1.32.0/autoload.php';

        $this->CI->load->model('tickets/tickets_model');
        $this->CI->load->model('tickets/tickets_log_model');

        $ticket = $this->CI->tickets_model->where('flag', ['PROGRESS','FINISHED','CLOSED','HOLD'])->get(['id' => $id]);
        if ($ticket)
        {
            $logEvents = $this->CI->tickets_log_model
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

                if (empty($dateOpened) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
                    foreach ($logEvents as $event) {
                        if ($event['event'] === TICKET_EVENT_REQUEST) {
                            $dateOpened = new \Carbon\Carbon($event['event_date']);
                        }
                    }
                }

                if (empty($dateHelpdeskResponse) && $ticket->flag !== TICKET_FLAG_REQUESTED) {
                    $dateHelpdeskResponse = $dateOpened;
                }

                // if date opened empty
                if (empty($ticket->date_opened)) 
                {
                    $this->CI->tickets_model->update([
                        'date_opened' => empty($dateOpened) ? null : $dateOpened->format('Y-m-d H:i:s'),
                        'response_helpdesk' => $dateRequested->diffInSeconds($dateHelpdeskResponse),
                    ], ['id' => $ticket->id]);
                }

                // get duration pic response
                $responseStaff = 0;
                foreach ($logEvents as $event) 
                {
                    if ($event['event'] === TICKET_EVENT_STAFF_RESPONSE) 
                    {
                        $beforeCurrentEvent = array_filter($logEvents, function ($row) use ($event) {
                            return $row['event'] === 'TICKET_ADD_STAFF' && $row['event_to'] === $event['event_by'];
                        });

                        if (count($beforeCurrentEvent) <= 0) {
                            continue;
                        }

                        $beforeCurrentEvent = array_values($beforeCurrentEvent);
                        $eventStart = new \Carbon\Carbon($beforeCurrentEvent[0]['event_date']);
                        $eventEnd = new \Carbon\Carbon($event['event_date']);
                        $responseStaff = $eventStart->diffInSeconds($eventEnd);

                        break;
                    }
                }

                // if date opened empty
                if (empty($ticket->response_pic)) {
                    $this->CI->tickets_model->update([
                        'response_pic' => $responseStaff,
                    ], ['id' => $ticket->id]);
                }

//                if ($ticket->flag === TICKET_FLAG_FINISHED)
//                {
                // begin get duration working time
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

                if ($filterFinished) {
                    $durationWorkingTime = $dateAccept->diffInSeconds($dateFinished);
                } else {
                    $durationWorkingTime = $dateAccept->diffInSeconds();
                }
                // end get duration working time

                // begin get duration hold time
                $filterEventHold = array_filter($logEvents, function ($row) use($filterAccept) {
                    return $row['event'] === TICKET_EVENT_HOLD && $row['id'] > $filterAccept['id'];
                });
                $filterEventHold = array_values($filterEventHold);
                $intervalHold = 0;
                $lastNextEvent = 0;
                foreach ($filterEventHold as $key => $eventHold) {
                    if (($key === 0) || ($lastNextEvent > 0 && $key > 0)) {
                        $nextEvent = array_filter($logEvents, function ($rowNext) use ($eventHold, $lastNextEvent) {
                            // return $rowNext['event'] === TICKET_EVENT_PROGRESS && $rowNext['id'] > $eventHold['id'];
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
                // end get duration hold time

                $this->CI->tickets_model->update([
                    'duration_work' => $durationWorkingTime,
                    'duration_hold' => $intervalHold,
                ], ['id' => $ticket->id]);
//                }

                if ($ticket->flag === TICKET_FLAG_CLOSED && !empty($dateClosed))
                {
                    $this->CI->tickets_model->update([
                        'duration' => $dateRequested->diffInSeconds($dateClosed),
                        'date_closed' => $dateClosed->format('Y-m-d H:i:s'),
                    ], ['id' => $ticket->id]);
                }
            }
        }
    }

    public function notifTelegramRequestTicket($ticketId)
    {
        $query = $this->CI->db->where(['id' => $ticketId])
            ->get('tickets_view');
        $ticketView = $query->row();

        if ($ticketView)
        {
            $textMessage = '*Notifikasi Permintaan Tiket*';
            $textMessage .= PHP_EOL . PHP_EOL . $ticketView->subject;
            $textMessage .= PHP_EOL . '#'. $ticketView->number;
            $textMessage .= PHP_EOL . PHP_EOL . $ticketView->description;
            $textMessage .= PHP_EOL . PHP_EOL . '_Pengirim:_';
            $textMessage .= PHP_EOL . sprintf('%s (%s)', $ticketView->informer_full_name, $ticketView->informer_email);
            if ($ticketView->informer_phone) {
                $textMessage .= PHP_EOL . $ticketView->informer_phone;
            }
            $textMessage .= PHP_EOL . sprintf('%s (%s)', $ticketView->company_name, $ticketView->company_branch_name);
            $textMessage .= PHP_EOL . PHP_EOL . sprintf('[Selengkapnya](https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/acp???/tickets/view/%s)', $ticketView->id);

            $this->CI->load->model('users/group_model');
            $helpdeskGroup = $this->CI->group_model
                ->as_array()
                ->get_all(['is_helpdesk' => 1]);

            $groupIds = [];
            foreach ($helpdeskGroup as $group) {
                if (!in_array($group['id'], $groupIds)) {
                    array_push($groupIds, $group['id']);
                }
            }

            $this->CI->load->model('users/user_model');
            $userHelpdesk = $this->CI->user_model->fields('id,username,telegram_user')
                ->where('group_id', $groupIds)
                ->as_array()
                ->get_all();

            if ($userHelpdesk) {
                foreach ($userHelpdesk as $user)
                {
                    if ($user['telegram_user'])
                    {
                        $dataMessage = [
                            'chat_id' => $user['telegram_user'],
                            'parse_mode' => 'MARKDOWN',
                            'text' => $textMessage
                        ];

                        try {
                            \Longman\TelegramBot\Request::sendMessage($dataMessage);
                        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
                            error_log('ERROR', $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    public function notifTelegramToEndUser($ticketId, $notifMode = 'FINISHED', $reason = '')
    {
        $query = $this->CI->db->where(['id' => $ticketId])
            ->get('tickets_view');
        $ticketView = $query->row();

        if ($ticketView->telegram_user)
        {
            if ($notifMode === 'REQUESTED') {
                $textMessage = '*Anda telah melakukan "Request tiket" pada sistem helpdesk (macca) ICT Kalla Group*';
            }
            else if ($notifMode === 'HELPDESK_RESPONSE') {
                $textMessage = '*Tiket anda sementara di response oleh helpdesk kami, silahkan menunggu anda akan segera dihubungi*';
            }
            else if ($notifMode === 'APPROVED') {
                $textMessage = '*Tiket anda telah di terima oleh helpdesk kami*';
            }
            else if ($notifMode === 'ACCEPTED') {
                $textMessage = '*Tiket anda telah diterima oleh petugas kami dan akan segera di kerjakan*';
            }
            else if ($notifMode === 'PROGRESS') {
                $textMessage = '*Perubahan status progress pada tiket anda*';
            }
            else if ($notifMode === 'HOLD') {
                $textMessage = '*Perubahan status hold pada tiket anda*';
            }
            else {
                $textMessage = '*Tiket anda telah selesai dikerjakan oleh PIC, silahkan menunggu helpdesk kami untuk melakukan konfirmasi.*';
            }

            $textMessage .= PHP_EOL . PHP_EOL . $ticketView->subject;
            $textMessage .= PHP_EOL . '#' . $ticketView->number;
            $textMessage .= PHP_EOL . PHP_EOL . $ticketView->description;
            $textMessage .= PHP_EOL . PHP_EOL . '_Kategori:_';
            $textMessage .= PHP_EOL . sprintf('%s > %s', $ticketView->category_name, $ticketView->category_sub_name);
            if ($notifMode === 'FINISHED' || $notifMode === 'PROGRESS') {
                $textMessage .= PHP_EOL . PHP_EOL . '_Keterangan:_';
                $textMessage .= PHP_EOL . $reason;
            }
            $textMessage .= PHP_EOL . PHP_EOL . '[Selengkapnya](https://helpdesk.kallagroup.co.id/login)';

            $dataMessage = [
                'chat_id' => $ticketView->telegram_user,
                'parse_mode' => 'MARKDOWN',
                'text' => $textMessage,
            ];

            try {
                \Longman\TelegramBot\Request::sendMessage($dataMessage);
            } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
                error_log('ERROR', json_encode($e));
            }
        }
    }
}