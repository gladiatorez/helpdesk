<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;


class RegisterCommand extends UserCommand
{
    protected $name = 'register';
    protected $description = 'Registrasi email macca anda, untuk mendapatkan notifikasi';
    protected $usage = '/register';
    protected $version = '1.1.0';
    protected $need_mysql = true;
    protected $private_only = true;

    protected $conversation;

    protected $error = '';

    public function execute()
    {
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

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
            case 0: // step email
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Untuk membatalkan perintah silahkan ketik /cancel'.PHP_EOL.'Masukkan email yang terdaftar di sistem macca:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }

                if (!$this->validateEmail($text)) {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = $this->error.PHP_EOL.'Masukkan email yang terdaftar di sistem macca:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['email'] = $text;
                $text = '';

            case 1: // step password
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Masukkan password anda:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }
                $notes['password'] = $text;

            case 2:
                $this->conversation->update();

                $login = $this->validateAccount($notes['email'], $notes['password'], $user_id);
                if ($login) {
                    $out_text = 'And telah berhasil melakukan registrasi';
                }
                else {
                    $out_text = 'Email dan password anda tidak terdaftar di sistem macca.'.PHP_EOL;
                    $out_text .= 'Silahkan ulangi kemali melakukan registrasi';
                }

                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                // $data['parse_mode'] = 'MARKDOWN';
                $data['text'] = $out_text;

                $result = Request::sendMessage($data);

                $this->conversation->stop();
                break;
        }

        return $result;
    }

    public function validateEmail($email)
    {
        if (!empty($email))
        {
            $ci = get_instance();
            $ci->load->model('users/user_model');
            $user = $ci->user_model->get(['email' => $email]);
            if ($user) {
                if (empty($user->telegram_user)) {
                    return true;
                }
                else {
                    $this->error = 'Email anda sudah melakukan registrasi sebelumnya';
                    return false;
                }
            }
        }

        $this->error = 'Email tidak terdaftar';
        return false;
    }

    public function validateAccount($email, $password, $userId)
    {
        if (!empty($email) && !empty($password))
        {
            $ci = get_instance();
            $ci->load->library('auth/the_auth_backend');
            $isUserBackend = $ci->the_auth_backend->doLoginTelegram($email, $password, $userId);
            if (!$isUserBackend) {
                $ci->load->library('auth/the_auth_frontend');
                return $ci->the_auth_frontend->doLoginTelegram($email, $password, $userId);
            }

            return $isUserBackend;
        }
    }
}
