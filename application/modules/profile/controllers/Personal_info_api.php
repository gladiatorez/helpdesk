<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Personal_info_api
 *
 * @property Informer_model $informer_model
 * @property User_model $user_model
 * @property The_auth_frontend $the_auth_frontend
 */
class Personal_info_api extends Informer_Api_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('informer/informer_model');
    }

    public function index()
    {
        $accountProfile = $this->the_auth_frontend->getUserLogin();

        if (!$accountProfile) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->model('references/company_model');
        $company = $this->company_model->set_cache('company_' . $accountProfile->informer->company_id)
            ->get(['id' => $accountProfile->informer->company_id]);
        $this->load->model('references/company_branch_model');
        $branch = $this->company_branch_model->get(['id' => $accountProfile->informer->company_branch_id]);
        $this->load->model('references/department_model');
        $department = $this->department_model->set_cache('department_' . $accountProfile->informer->department_id)
            ->get(['id' => $accountProfile->informer->department_id]);

        $row = new stdClass();
        $row->fullName = $accountProfile->informer->full_name;
        $row->phone = $accountProfile->informer->phone;
        $row->nik = $accountProfile->informer->nik;
        $row->position = $accountProfile->informer->position;
        $row->companyId = $accountProfile->informer->company_id;
        $row->companyName = $company ? $company->name : $accountProfile->informer->company_other;
        $row->companyBranchId = $accountProfile->informer->company_branch_id;
        $row->companyBranchName = $branch ? $branch->name : '';
        $row->departmentId = $accountProfile->informer->department_id;
        $row->departmentName = $department ? $department->name : $accountProfile->informer->department_other;
        $row->email = $accountProfile->email;
        $row->username = $accountProfile->username;
        $row->lastLogin = $accountProfile->last_login;

        $this->template->build_json([
            'success' => true,
            'row' => $row
        ]);
    }

    public function companyoptions()
    {
        // get companies
        $this->load->model('references/company_model');
        $companies = $this->company_model->fields('id, name')
            ->order_by('name')
            ->as_array()
            ->with('branch', ['fields:id,name'])
            ->get_all(['active' => 'A']);

        // get departments
        $this->load->model('references/department_model');
        $departments = $this->department_model->fields('id,name')
            ->order_by('name')
            ->as_array()
            ->get_all();

        $this->template->build_json([
            'companyOptions' => $companies ? $companies : [],
            'departmentOptions' => $departments ? $departments : [],
        ]);
    }

    public function savegeneral()
    {
        $userId = $this->the_auth_frontend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::saving_failed')
            ]);
            return false;
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('fullName', 'Full name', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required|max_length[15]');
        if ($this->form_validation->run()) {
            $data = [
                'full_name'     => $this->input->post('fullName', true),
                'phone'         => $this->input->post('phone'),
            ];

            $result = $this->informer_model->update($data, ['user_id' => $userId]);
            if ($result) {
                $this->load->model('users/user_model');
                $this->user_model->delete_cache('*');
                Events::trigger('frontend::informer:personalinfo_updated', $userId);
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function savecompany()
    {
        $userId = $this->the_auth_frontend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::saving_failed')
            ]);
            return false;
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('companyId', 'Company', 'trim|required');
        $this->form_validation->set_rules('companyBranchId', 'Location', 'trim|required');
        $this->form_validation->set_rules('departmentId', 'Department', 'trim|required');
        $this->form_validation->set_rules('nik', 'NIK', 'trim|required|max_length[20]|callback__check_nik');
        $this->form_validation->set_rules('position', 'Position', 'trim|required|max_length[255]');
        if ($this->input->post('departmentId') === '-1') {
            $this->form_validation->set_rules('departmentOther', 'Other Department', 'trim|required|max_length[255]');
        }
        if ($this->form_validation->run()) {
            $data = [
                'nik' => $this->input->post('nik', true),
                'position' => $this->input->post('position'),
                'company_id' => $this->input->post('companyId'),
                'company_branch_id' => $this->input->post('companyBranchId'),
                'department_id' => $this->input->post('departmentId'),
            ];
            if ($this->input->post('departmentId') === '-1') {
                $data['department_other'] = $this->input->post('departmentOther', TRUE);
            }

            $result = $this->informer_model->update($data, ['user_id' => $userId]);
            if ($result) {
                $this->load->model('users/user_model');
                $this->user_model->delete_cache('*');
                Events::trigger('frontend::informer:personalinfo_updated', $userId);
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function _check_nik($nik = '')
    {
        $userId = $this->the_auth_frontend->getUserLoginId();
        if ($this->informer_model->with_trashed()->check_unique_field('nik', $nik, $userId, 'user_id')) {
            $this->form_validation->set_message('_check_nik', sprintf(lang('profile::msg:nik_already_exist'), $nik));
            return false;
        }

        return true;
    }

    public function saveaccount()
    {
        $userId = $this->the_auth_frontend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::saving_failed')
            ]);
            return false;
        }

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('email', 'Email', 'trim|required|max_length[100]|valid_email|callback__check_email');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|max_length[20]|min_length[3]|callback__check_username');
        $changePassword = (bool) $this->input->post('changePassword');
        if ($changePassword) {
            $passMinLength = $this->the_auth_frontend->getMinPasswordLength();
            $passMaxLength = $this->the_auth_frontend->getMaxPasswordLength();
            $this->form_validation->set_rules('oldPassword', 'Current password', 'trim|required|callback__check_old_password');
            $this->form_validation->set_rules('newPassword', 'New password', 'trim|required|min_length[' . $passMinLength . ']|max_length[' . $passMaxLength . ']');
            $this->form_validation->set_rules('newPasswordConfirm', 'Confirm new password', 'trim|required|matches[newPassword]');
            
        }
        if ($this->form_validation->run()) {
            $data = [
                'email' => $this->input->post('email', true),
                'username' => $this->input->post('username', true),
            ];

            $result = $this->the_auth_frontend->updateAccount($userId, $data, $changePassword, $this->input->post('newPassword'));
            if ($result) {
                $this->load->model('users/user_model');
                $this->user_model->delete_cache('*');
                Events::trigger('frontend::informer:personalinfo_updated', $userId);
                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }

    public function _check_email($email = '')
    {
        if ($email) {
            $userId = $this->the_auth_frontend->getUserLoginId();
            if ($this->user_model->with_trashed()->check_unique_field('email', $email, $userId, 'id')) {
                $this->form_validation->set_message('_check_email', sprintf(lang('profile::msg:email_already_exist'), $email));
                return false;
            }

            $this->load->model('references/email_list_model');
            $find = $this->email_list_model->count_rows(['email' => $email]);
            if ($find <= 0) {
                $this->form_validation->set_message('_check_email', 'Email not found in list email system');
                return false;
            }
        }

        return true;
    }

    public function _check_username($username = '')
    {
        $userId = $this->the_auth_frontend->getUserLoginId();
        if ($this->user_model->with_trashed()->check_unique_field('username', $username, $userId, 'id')) {
            $this->form_validation->set_message('_check_username', sprintf(lang('profile::msg:username_already_exist'), $username));
            return false;
        }

        return true;
    }

    public function _check_old_password($password = '')
    {
        if (!empty($password)) 
        {
            $userId = $this->the_auth_frontend->getUserLoginId();

            if (!$this->the_auth_frontend->hashPasswordDb($userId, $password)) 
            {
                $this->form_validation->set_message('_check_old_password', lang('profile::msg:wrong_old_password'));
                return false;
            }
        }
        return true;
    }

    public function history()
    {
        $userId = $this->the_auth_frontend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }
        
        
        $this->load->model('informer/informer_view_model');
        $informer = $this->informer_view_model->fields('id,user_id')
            ->get(['user_id' => $userId]);
        if (!$informer) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->library('bt_server');
        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'uid', 'bt' => 'uid'],
            ['db' => 'number', 'bt' => 'number'],
            ['db' => 'subject', 'bt' => 'subject'],
            ['db' => 'description', 'bt' => 'descr'],
            ['db' => 'flag', 'bt' => 'flag'],
            ['db' => 'company_name', 'bt' => 'companyName'],
            ['db' => 'company_branch_name', 'bt' => 'companyBranchName'],
            ['db' => 'department_name', 'bt' => 'departmentName'],
            ['db' => 'department_other', 'bt' => 'departmentOther'],
            ['db' => 'priority_name', 'bt' => 'priorityName'],
            ['db' => 'informer_full_name', 'bt' => 'informerFullName'],
            ['db' => 'informer_user_id', 'bt' => 'userId'],
            ['db' => 'informer_id', 'bt' => 'informerId'],
            ['db' => 'category_name', 'bt' => 'categoryName'],
            ['db' => 'category_sub_name', 'bt' => 'categorySubName'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $this->load->model('tickets/tickets_model');
        $request = $this->bt_server->addFilterRequest('informerId', $informer->id, $request, 'in');
        $request = $this->bt_server->addFilterRequest('flag', 'CLOSED', $request, 'in');
        $results = $this->bt_server
            ->withTrashed()
            ->process($request, $columns, $this->tickets_model->table. '_view');
        $this->template->build_json($results);
    }

    public function currentticket()
    {
        $userId = $this->the_auth_frontend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->model('informer/informer_view_model');
        $informer = $this->informer_view_model->fields('id,user_id')
            ->get(['user_id' => $userId]);
        if (!$informer) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->library('bt_server');
        $request = $this->input->get();
        $columns = [
            ['db' => 'id', 'bt' => 'id'],
            ['db' => 'uid', 'bt' => 'uid'],
            ['db' => 'number', 'bt' => 'number'],
            ['db' => 'subject', 'bt' => 'subject'],
            ['db' => 'description', 'bt' => 'descr'],
            ['db' => 'flag', 'bt' => 'flag'],
            ['db' => 'company_name', 'bt' => 'companyName'],
            ['db' => 'department_name', 'bt' => 'departmentName'],
            ['db' => 'department_other', 'bt' => 'departmentOther'],
            ['db' => 'priority_name', 'bt' => 'priorityName'],
            ['db' => 'informer_full_name', 'bt' => 'informerFullName'],
            ['db' => 'informer_user_id', 'bt' => 'userId'],
            ['db' => 'informer_id', 'bt' => 'informerId'],
            ['db' => 'category_name', 'bt' => 'categoryName'],
            ['db' => 'category_sub_name', 'bt' => 'categorySubName'],
            ['db' => 'created_at', 'bt' => 'createdAt'],
            ['db' => 'updated_at', 'bt' => 'updatedAt'],
        ];

        $this->load->model('tickets/tickets_model');
        $request = $this->bt_server->addFilterRequest('informerId', $informer->id, $request, 'in');
        $request = $this->bt_server->addFilterRequest('flag', ['REQUESTED', 'OPENED', 'PROGRESS', 'FINISHED', 'HOLD'], $request, 'in');
        $results = $this->bt_server
            ->withTrashed()
            ->process($request, $columns, $this->tickets_model->table . '_view');
        $this->template->build_json($results);
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

        $userId = $this->the_auth_frontend->getUserLoginId();
        if (!$userId) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $id = $this->input->get('id', true);
        $this->load->model('tickets/tickets_view_model');
        $item = $this->tickets_view_model
            ->as_array()
            ->with('staffs')
            ->with('logs', ['order_inside:id desc, event_date desc'])
            ->with('notes')
            ->with('attachment')
            ->with('part_list')
            ->with('part_photos')
            ->with_work_result([
                'fields' => 'id,ticket_id,file_id',
                'with' => [
                    'relation' => 'file',
                    'fields' => 'id,name,extension'
                ]
            ])
            ->get(['uid' => $id]);
        if (!$item) {
            $this->output->set_status_header('404');
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_empty')
            ]);
            return false;
        }

        $this->load->library('references/keyword');
        $keywords = Keyword::getArray($item['keywords']);

        $this->load->model('tickets/tickets_comment_model');
        $item['comments'] = $this->tickets_comment_model->getAllByTicket($item['id']);

        $data = $item;
        $data['keywords'] = $keywords;

        $this->template->build_json([
            'success' => true,
            'data' => $data
        ]);
        return true;
    }

    public function ticket_add_comment()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->form_validation->set_rules('ticketId', 'ID', 'trim|required');
        $this->form_validation->set_rules('comments', 'comments', 'trim|required');
        if ($this->form_validation->run()) {
            $data = [
                'ticket_id' => $this->input->post('ticketId', true),
                'comments' => $this->input->post('comments', true),
            ];

            $this->load->model('tickets/tickets_comment_model');
            $result = $this->tickets_comment_model->create($data);
            if ($result) {
                // send to socketio
                $this->load->model('tickets/tickets_comment_model');
                $data = $this->tickets_comment_model->getById($result);
                if ($data) 
                {
                    $this->load->model('tickets/tickets_staff_view_model');
                    $staffs = $this->tickets_staff_view_model->get_all(['ticket_id' => $data->ticket_id]);

                    $this->load->model('tickets/tickets_view_model');
                    $ticket = $this->tickets_view_model->get(['id' => $data->ticket_id]);

                    // send notif telegram to staff
                    if ($staffs && $ticket) {
                        foreach ($staffs as $staff) {
                            if ($data->$data->created_by_staff) {
                                $fullName = $data->created_by_staff === $staff->full_name ? 'Anda' : $data->created_by_staff;
                            } else if ($data->$data->created_by_infomer) {
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

                    $client = stream_socket_client($this->config->item('socket_address'));

                    $data_socket = [
                            'channel'   => '',
                            // 'event'     => sprintf('push-comment--' . $data->ticket_id),
                            'event'     => sprintf('pushComment'),
                            'data'      => $data
                    ];
                    $buffer = json_encode($data_socket)."\n";

                    fwrite($client, $buffer);
                }

                $this->template->build_json([
                    'success' => true,
                    'message' => lang('msg::saving_success')
                ]);
            } else {
                $this->template->build_json([
                    'success' => false,
                    'message' => lang('msg::saving_failed')
                ]);
            }
        } else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
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
}