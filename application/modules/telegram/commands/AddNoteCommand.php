<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;


class AddNoteCommand extends UserCommand
{
    protected $name = 'addnote';
    protected $description = "Menambahkan 'note' pada tiket macca";
    protected $usage = '/addnote';
    protected $version = '1.1.0';
    protected $need_mysql = true;
    protected $private_only = true;

    protected $conversation;

    protected $error = '';

    public function execute()
    {
        $message = $this->getMessage();
        $chat = $message->getChat();
        $user = $message->getFrom();
        $chat_id = $chat->getId();
        $text = trim($message->getText(true));
        $user_id = $user->getId();
        $ci = get_instance();

        $data = [
            'chat_id' => $chat_id,
        ];

        Request::sendChatAction(['chat_id' => $chat_id, 'action' => 'typing']);

        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        $state = 0;
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }

        $result = Request::emptyResponse();

        switch ($state) {
            case 0: // choose ticket number
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $queryStaff = $ci->db->where('telegram_user', $user_id)
                        ->where_not_in('ticket_flag', [TICKET_FLAG_CLOSED, TICKET_FLAG_FINISHED])
                        ->get('tickets_staff_view');
                    $resultStaff = $queryStaff->result();
                    if ($resultStaff) {
                        $data['text'] = 'Untuk membatalkan perintah silahkan ketik /cancel'.PHP_EOL.'Pilih nomor tiket:';
                        $ticketIds = [];
                        foreach ($resultStaff as $staff) {
                            if (!in_array($staff->ticket_id, $ticketIds)) {
                                array_push($ticketIds, $staff->ticket_id);
                            }
                        }

                        $queryTickets = $ci->db->select('number')->where_in('id', $ticketIds)
                            ->where_not_in('flag', [TICKET_FLAG_CLOSED, TICKET_FLAG_FINISHED])
                            ->order_by('number')
                            ->get('uf_tickets');
                        $ticketNumbers = [];
                        foreach ($queryTickets->result() as $ticket) {
                            if (!in_array($ticket->number, $ticketNumbers)) {
                                array_push($ticketNumbers, $ticket->number);
                            }
                        }
                        $keyboards = new Keyboard($ticketNumbers);
                        $keyboard = $keyboards->setResizeKeyboard(true)
                            ->setOneTimeKeyboard(true)
                            ->setSelective(false);
                        $data['reply_markup'] = $keyboard;
                    }
                    else {
                        $this->conversation->cancel();
                        $data['text'] = 'Sementara anda tidak memiliki tiket untuk dikerjakan';
                        $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    }

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['ticket_number'] = $text;
                $text = '';

            case 1: // add note
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Masukkan note untuk tiket yang telah dipilih:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }
                $notes['note'] = $text;

            case 2:
                $this->conversation->update();

                $ticket = $ci->db->where('number', $notes['ticket_number'])->get('uf_tickets')->row();
                $user = $ci->db->where('telegram_user', $user_id)->get('uf_user_users')->row();
                if ($ticket && $user) {
                    $dataNote = [
                        'ticket_id' => $ticket->id,
                        'description' => $notes['note'],
                        'user_id' => $user->id,
                    ];

                    $ci->load->model('tickets/tickets_note_model');
                    $insert = $ci->tickets_note_model->insert($dataNote);

                    $out_text = 'Tidak dapat menambahkan note pada tiket silahkan ulangi kembali';
                    if ($insert) {
                        $out_text = 'Anda telah berhasil menambahkan note pada tiket #'.$notes['ticket_number'];
                    }

                    $data['text'] = $out_text;
                    $result = Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }

                break;
        }

        return $result;
    }
}
