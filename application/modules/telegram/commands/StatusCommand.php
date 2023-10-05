<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

/**
 * User "/status" command
 */
class StatusCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'status';

    /**
     * @var string
     */
    protected $description = 'Periksa status terakhir ticket macca';

    /**
     * @var string
     */
    protected $usage = '/status <nomor tiket>';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $ci = get_instance();
        $message = $this->getMessage();
        $message_id = $message->getMessageId();
        $chat_id = $message->getChat()->getId();
        $text    = trim($message->getText(true));

        $user_id = $message->getFrom()->getId();
        $hasUser = $ci->db->where('telegram_user', $user_id)
            ->count_all_results('user_users');

        if ($hasUser > 0) {
            if ($text === '') {
                $text = 'Command usage: ' . $this->getUsage();
            }
            else {
                // posting ticket
                $postingTicketGet = $ci->db->select('id')->where(['number' => $text])
                    ->get('tickets');
                $postingTicketQuery = $postingTicketGet->row();
                if ($postingTicketQuery) {
                    $ci->load->library('tickets/the_tickets');
                    $ci->the_tickets->ticketPosting($postingTicketQuery->id);
                }


                $query = $ci->db->where(['number' => $text])
                    ->get('tickets_view');
                $ticket = $query->row();

                if ($ticket) {
                    $text = $this->formattedTicket($ticket);
                    $text .= $this->formattedFlagTicket($ticket->flag);

                    // sebagai pengirim tiket
                    if ($ticket->telegram_user == $user_id) {
                        $text .= $this->formattedUrlTicket($ticket, true);
                    }
                    else {
                        // sebagai pic tiket
                        $queryStaff = $ci->db->where('telegram_user', $user_id)
                            ->where('ticket_id', $ticket->id)
                            ->get('tickets_staff_view');
                        $ticketStaff = $queryStaff->row();

                        if ($ticketStaff) {
                            if ($ticket->flag === TICKET_FLAG_PROGRESS || $ticket->flag === TICKET_FLAG_HOLD) {
                                $text .= $this->formattedCommandTicket($ticket->number);
                            }
//                            else if ($ticket->flag === TICKET_FLAG_CLOSED) {
                                $text .= $this->formattedLeadTimeTicket($ticket->duration_work, $ticket->duration_hold);
//                            }

                            $text .= $this->formattedUrlTicket($ticket);
                        }
                        else {
                            // sebagai helpdesk
                            $isHelpdesk = $ci->db->where('telegram_user', $user_id)
                                ->select('user_users.id')
                                ->from('user_users')
                                ->join('user_groups', 'user_users.group_id = user_groups.id', 'left')
                                ->where('user_groups.is_helpdesk', '1')
                                ->count_all_results();
                            if ($isHelpdesk) {
                                $text .= $this->formattedUrlTicket($ticket, false, true);
                            }
                            else {
                                $text = 'Tiket tidak ditemukan';
                            }
                        }
                    }
                }
                else {
                    $text = 'Tiket tidak ditemukan';
                }
            }
        }
        else {
            $text = 'Anda tidak berhak untuk mengakses perintah ini, silahkan registrasi terlebih dahulu';
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
            'parse_mode' => 'MARKDOWN',
            'reply_to_message_id' => $message_id
        ];

        return Request::sendMessage($data);
    }

    public function formattedTicket($ticket, $title = '*Ticket Macca*')
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

    public function formattedUrlTicket($ticket, $endUser = false, $helpdesk = false)
    {
        if ($endUser) {
            return PHP_EOL . PHP_EOL . sprintf('[Got to macca system](https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/account???/view/%s)', $ticket->uid);
        }

        if ($helpdesk) {
            return PHP_EOL . PHP_EOL . sprintf('[Got to macca system](https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/acp???/tickets/view/%s)', $ticket->id);
        }

        return PHP_EOL . PHP_EOL . sprintf('[Got to macca system](https://helpdesk.kallagroup.co.id/telegram_redirect?url=helpdesk.kallagroup.co.id/acp???/queues/assignment/view/%s)', $ticket->id);
    }

    public function formattedFlagTicket($flag)
    {
        $textMessage = PHP_EOL . PHP_EOL . '_Status:_';
        $textMessage .= PHP_EOL . $flag;

        return $textMessage;
    }

    public function formattedLeadTimeTicket($durationWork, $durationHold)
    {
        $leadTimeDuration = $durationWork - $durationHold;

        $textMessage = PHP_EOL . PHP_EOL . '_Lead Time:_';
        $textMessage .= PHP_EOL . $leadTimeDuration . ' Detik';

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
