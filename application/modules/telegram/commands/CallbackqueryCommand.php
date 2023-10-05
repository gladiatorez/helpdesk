<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $ci = get_instance();

        $callbackQuery    = $this->getCallbackQuery();
        $callbackData     = $callbackQuery->getData();
        $callbackDataObj = json_decode($callbackData);

        if (property_exists($callbackDataObj, 'tiketId'))
        {
            $this->ticketHandler($this->getCallbackQuery());
        }
    }

    /**
     * @param CallbackQuery $callbackQuery
     */
    public function ticketHandler($callbackQuery)
    {
        $ci = get_instance();

        $callbackData     = $callbackQuery->getData();
        $callbackDataObj = json_decode($callbackData);

        $message          = $callbackQuery->getMessage();
        $chat_id          = $message->getChat()->getId();
        $message_to_edit = $message->getMessageId();

        $query = $ci->db->where(['id' => $callbackDataObj->tiketId])
            ->get('tickets_view');
        $ticket = $query->row();

        $queryStaff = $ci->db->where('telegram_user', $chat_id)
            ->where('ticket_id', $callbackDataObj->tiketId)
            ->get('tickets_staff_view');
        $ticketStaff = $queryStaff->row();
        $isRead = (bool) $ticketStaff->is_read;
        $isClaimed = (bool) $ticketStaff->is_claimed;

        $dateEdit = [
            'chat_id'    => $chat_id,
            'message_id' => $message_to_edit,
            'parse_mode' => 'MARKDOWN',
        ];

        $callbackDataCommand = $callbackDataObj->command;

        switch ($callbackDataCommand) {
            case TICKET_EVENT_STAFF_RESPONSE:
                if (!$isRead)
                {
                    if ($ticket->flag !== TICKET_FLAG_FINISHED || $ticket->flag !== TICKET_FLAG_CLOSED)
                    {
                        $ci->load->library('tickets/the_tickets');
                        $insertEvent = $ci->the_tickets->insertEvent($ticket->id, TICKET_EVENT_STAFF_RESPONSE, 'user_id:' . $ticketStaff->user_id);
                        if ($insertEvent)
                        {
                            $dataUpdate = ['is_read' => 1];

                            $ci->load->model('tickets/tickets_log_model');
                            $responseEvent = $ci->tickets_log_model->fields('id,event_from_id,event_date')
                                ->get(['id' => $insertEvent]);
                            if ($responseEvent) {
                                $fromEvent = $ci->tickets_log_model->fields('id,event_from_id,event_date')
                                    ->get([
                                        'event_to' => $ticketStaff->user_id,
                                        'event' => TICKET_EVENT_ADD_STAFF,
                                    ]);

                                if ($fromEvent) {
                                    $responseTime = strtotime($responseEvent->event_date) - strtotime($fromEvent->event_date);
                                    $dataUpdate['response_time'] = $responseTime;
                                }
                            }

                            $ci->load->model('tickets/tickets_staff_model');
                            $ci->tickets_staff_model->update($dataUpdate, array('id' => $ticketStaff->id));
                        }
                    }
                }

                $inline_keyboard = new InlineKeyboard([
                    ['text' => 'Accept', 'callback_data' => json_encode([
                        'tiketId' => $ticket->id,
                        'command' => TICKET_EVENT_ACCEPT
                    ])],
                ]);
                $dateEdit['reply_markup'] = $inline_keyboard;

                $textMessage = $this->formattedTicket($ticket, '*Notifikasi Penambahan Staff Ticket*');
                $textMessage .= $this->formattedUrlTicket($ticket);
                $dateEdit['text'] = $textMessage;
            break;

            case TICKET_EVENT_ACCEPT:
                if (!$isClaimed) {
                    $ci->load->library('tickets/the_tickets');
                    $ci->the_tickets->acceptByStaffUserId($ticket->id, $ticketStaff->user_id);
                }

                $ticketRow = $this->getTicketFlag($callbackDataObj->tiketId);

                $textMessage = $this->formattedTicket($ticket);
                $textMessage .= $this->formattedFlagTicket($ticketRow->flag);
                $textMessage .= $this->formattedCommandTicket($ticket->number);
                $textMessage .= $this->formattedUrlTicket($ticket);
                $dateEdit['text'] = $textMessage;
            break;
        }

        return Request::editMessageText($dateEdit);
    }

    public function getTicketFlag($ticketId)
    {
        $ci = get_instance();
        $queryTicket = $ci->db->select('id,flag')->where(['id' => $ticketId])
            ->get('tickets');
        return $queryTicket->row();
    }

    public function formattedTicket($ticket, $title = '*Ticket Macca Anda*')
    {
        $textMessage = $title;
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

        return $textMessage;
    }

    public function formattedUrlTicket($ticket)
    {
        return PHP_EOL . PHP_EOL . sprintf('[Got to macca system](https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/acp???/queues/assignment/view/%s)', $ticket->id);
    }

    public function formattedFlagTicket($flag)
    {
        $textMessage = PHP_EOL . PHP_EOL . '_Status:_';
        $textMessage .= PHP_EOL . $flag;

        return $textMessage;
    }

    public function formattedCommandTicket($ticketNumber)
    {
        $textMessage = PHP_EOL . PHP_EOL . '_Perintah:_';
        $textMessage .= PHP_EOL . '/setprogress\_'. $ticketNumber . ' <alasan>';
        $textMessage .= PHP_EOL . '/sethold\_'. $ticketNumber . ' <alasan>';

        return $textMessage;
    }
}
