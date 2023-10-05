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
use Longman\TelegramBot\Request;

/**
 * Generic command
 *
 * Gets executed for generic commands, when no other appropriate one is found.
 */
class GenericCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'generic';

    /**
     * @var string
     */
    protected $description = 'Handles generic commands or is executed by default when a command is not found';

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
        $message = $this->getMessage();

        //You can use $command as param
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $command = $message->getCommand();
        $ci = get_instance();

        //If the user is an admin and the command is in the format "/whoisXYZ", call the /whois command
        if (stripos($command, 'whois') === 0 && $this->telegram->isAdmin($user_id)) {
            return $this->telegram->executeCommand('whois');
        }

        if (strpos($command, 'setprogress_') !== false && strlen($command) === 22) {
            return $this->setProgress();
        }

        if (strpos($command, 'sethold_') !== false  && strlen($command) === 18) {
            return $this->setHold();
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => 'Command /' . $command . ' not found.. :(',
        ];

        return Request::sendMessage($data);
    }

    public function setProgress()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $command = $message->getCommand();
        $ci = get_instance();

        $reason = $message->getText(true);
        $number = substr($command, 12);

        $query = $ci->db->select(['id','number','flag'])->where(['number' => $number])
            ->get('tickets');
        $ticket = $query->row();

        $queryStaff = $ci->db->where('telegram_user', $user_id)
            ->where('ticket_id', $ticket->id)
            ->get('tickets_staff_view');
        $ticketStaff = $queryStaff->row();

        if ($ticketStaff) {
            if ($ticket->flag === TICKET_FLAG_CLOSED) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => 'Tiket telah berstatus ' . $ticket->flag
                ]);
            }

            $ci->load->library('tickets/the_tickets');
            $changeStatus = $ci->the_tickets->changeStatus($ticket->id, TICKET_FLAG_PROGRESS, $ticketStaff->user_id, $reason, null, null, [], true);

            $data = [
                'chat_id' => $chat_id,
                'text' => $changeStatus
                    ? 'Berhasil merubah status ticket menjadi ' . TICKET_FLAG_PROGRESS
                    : $ci->the_tickets->getErrors()
            ];
        }
        else {
            $data = [
                'chat_id' => $chat_id,
                'text' => 'Anda tidak mempunyai akses untuk merubah status tiket'
            ];
        }

        $messageReply = $message->getReplyToMessage();
        if ($messageReply) {
            $data['reply_to_message_id'] = $messageReply->getMessageId();
        }

        return Request::sendMessage($data);
    }

    public function setHold()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $command = $message->getCommand();
        $ci = get_instance();

        $reason = $message->getText(true);
        $number = substr($command, 8);

        $query = $ci->db->select(['id','number','flag'])->where(['number' => $number])
            ->get('tickets');
        $ticket = $query->row();

        $queryStaff = $ci->db->where('telegram_user', $user_id)
            ->where('ticket_id', $ticket->id)
            ->get('tickets_staff_view');
        $ticketStaff = $queryStaff->row();

        if ($ticketStaff) {
            if ($ticket->flag === TICKET_FLAG_CLOSED) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => 'Tiket telah berstatus ' . $ticket->flag
                ]);
            }

            $ci->load->library('tickets/the_tickets');
            $changeStatus = $ci->the_tickets->changeStatus($ticket->id, TICKET_FLAG_HOLD, $ticketStaff->user_id, $reason, null, null, [], true);

            $data = [
                'chat_id' => $chat_id,
                'text' => $changeStatus
                    ? 'Berhasil merubah status ticket menjadi ' . TICKET_FLAG_HOLD
                    : $ci->the_tickets->getErrors()
            ];
        }
        else {
            $data = [
                'chat_id' => $chat_id,
                'text' => 'Anda tidak mempunyai akses untuk merubah status tiket'
            ];
        }

        $messageReply = $message->getReplyToMessage();
        if ($messageReply) {
            $data['reply_to_message_id'] = $messageReply->getMessageId();
        }

        return Request::sendMessage($data);
    }
}
