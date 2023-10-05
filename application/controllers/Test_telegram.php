<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Testing
 *
 * @property The_telebot $the_telebot
 * @property CI_DB_query_builder $db
 * @property Group_model $group_model
 * @property User_model $user_model
 * @property The_tickets $the_tickets
 */
class Test_telegram extends MY_Controller
{
	public $_themeName = 'frontend-theme';

	public function __construct()
    {
        parent::__construct();

        $this->load->library('telegram/the_telebot');
    }

    public function index()
    {
//        \Longman\TelegramBot\Request::sendMessage([
//            'chat_id' => 51041520,
//            'text' => 'Your utf8 text 😜 ...',
//            'reply_markup' => $inline_keyboard
//        ]);

        // status - Periksa status terakhir ticket macca
        // register - Registrasi email macca anda, untuk mendapatkan notifikasi
        // cancel - Cancel the currently active conversation
        // help - Show bot commands help
        // setprogress_ - Tiket akan berstatus progress, informasi selanjutnya ada di setiap tiket
        // sethold_ - Tiket akan berstatus hold, informasi selanjutnya ada di setiap tiket

         $query = $this->db->where(['id' => '6064'])
             ->get('tickets_view');
         $ticket = $query->row();

        /** template tiket */
        // $textMessage = '*'. $ticket->subject .'*';
        // $textMessage .= PHP_EOL . '#'. $ticket->number;
        // $textMessage .= PHP_EOL . PHP_EOL . $ticket->description;
        // $textMessage .= PHP_EOL . PHP_EOL . '_Pengirim:_';
        // $textMessage .= PHP_EOL . sprintf('%s (%s)', $ticket->informer_full_name, $ticket->informer_email);
        // if ($ticket->informer_phone) {
        //     $textMessage .= PHP_EOL . $ticket->informer_phone;
        // }
        // $textMessage .= PHP_EOL . sprintf('%s (%s)', $ticket->company_name, $ticket->company_branch_name);
        // $textMessage .= PHP_EOL . PHP_EOL . '_Kategori:_';
        // $textMessage .= PHP_EOL . sprintf('%s > %s', $ticket->category_name, $ticket->category_sub_name);
        // $textMessage .= PHP_EOL . PHP_EOL . sprintf('[Selengkapnya](https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/acp???/tickets/view/%s)', $ticket->id);

        /** Template notifikasi request tiket */
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

        $dataMessage = [
            'chat_id' => 51041520,
            'parse_mode' => 'MARKDOWN',
            'text' => $textMessage,
            'reply_markup' => $inlineKeyboard
        ];

        try {
            \Longman\TelegramBot\Request::sendMessage($dataMessage);
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            error_log('ERROR', json_encode($e));
        }

//        $this->template->build_json($ticket->row());
    }

    public function notif_enduser()
    {
        $query = $this->db->where(['id' => '7047'])
            ->get('tickets_view');
        $ticketView = $query->row();

        if ($ticketView->telegram_user) {
//            if ($notifMode === 'REQUESTED') {
                $textMessage = '*Anda telah melakukan "Request tiket" pada sistem helpdesk (macca) ICT Kalla Group*';
//            }
//            else if ($notifMode === 'APPROVED') {
//                $textMessage = '*Tiket anda telah di terima oleh helpdesk kami*';
//            }
//            else if ($notifMode === 'ACCEPTED') {
//                $textMessage = '*Tiket anda telah diterima oleh petugas kami dan akan segera di kerjakan oleh petugas kami*';
//            }
//            else {
//                $textMessage = '*Tiket anda telah selesai dikerjakan oleh petugas kami, silahkan menunggu helpdesk kami akan melakukan konfirmasi.*';
//            }

            $textMessage .= PHP_EOL . PHP_EOL . $ticketView->subject;
            $textMessage .= PHP_EOL . '#' . $ticketView->number;
            $textMessage .= PHP_EOL . PHP_EOL . $ticketView->description;
            $textMessage .= PHP_EOL . PHP_EOL . '_Kategori:_';
            $textMessage .= PHP_EOL . sprintf('%s > %s', $ticketView->category_name, $ticketView->category_sub_name);
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

    public function test_redirect()
    {
        // https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/acp???/tickets/view/6784

        $this->template->build_json([
            $_SERVER
        ]);
    }

    public function test_get_user_helpdesk()
    {
        $this->load->model('users/group_model');

        $helpdeskGroup = $this->group_model
            ->as_array()
            ->get_all(['is_helpdesk' => 1]);

        $groupIds = [];
        foreach ($helpdeskGroup as $group) {
            if (!in_array($group['id'], $groupIds)) {
                array_push($groupIds, $group['id']);
            }
        }

        $this->load->model('users/user_model');
        $userHelpdesk = $this->user_model->fields('id,username,telegram_user')
            ->where('group_id', $groupIds)
            ->as_array()
            ->get_all();

        die(json_encode($userHelpdesk));
    }

    public function test_send_notif()
    {
        $this->load->library('tickets/the_tickets');
        $this->the_tickets->notifTelegramRequestTicket('6816');
    }
}
