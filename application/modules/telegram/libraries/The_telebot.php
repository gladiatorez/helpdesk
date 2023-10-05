<?php

/**
 * Class The_telebot
 *
 */
class The_telebot
{
    protected $botKey = '1163158416:AAE3nZ2q2DjzU0VFaYmynkVnmpb7tqtxJ7c';
    protected $botName = 'CICT_MaccaBot';
    protected $hooUrl = 'https://helpdesk.kallagroup.co.id/telegram/hook';

    protected $telegram;
    protected $ci;

    public function __construct()
    {
        $this->ci = get_instance();
        try {
            $this->telegram = new \Longman\TelegramBot\Telegram($this->botKey, $this->botName);
            $this->telegram->enableAdmin(51041520);
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            log_message('error', $e);
        }
    }

    public function getUpdates()
    {
        $commandPath = __DIR__ . '/../commands/';
        try {
            $mysqlCredentials = [
                'host'     => 'localhost',
                'user'     => 'root',
                'password' => 'Allah',
                'database' => 'kalla_macca',
            ];
            
            $this->telegram->enableMySql($mysqlCredentials, 'uf_telegram_');
            $this->telegram->addCommandsPath($commandPath);
            
            $this->telegram->enableLimiter();
            $serverResponse = $this->telegram->handleGetUpdates();
            if ($serverResponse->isOk()) {
                $update_count = count($serverResponse->getResult());
                echo date('Y-m-d H:i:s', time()) . ' - Processed ' . $update_count . ' updates';
            } else {
                echo date('Y-m-d H:i:s', time()) . ' - Failed to fetch updates' . PHP_EOL;
                echo $serverResponse->printError();
            }
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            log_message('error', $e);
        }
    }

    public function setWebHook()
    {
        try {
            $result = $this->telegram->setWebhook($this->hooUrl, [
                'certificate' => APPPATH . '/../ssl/2021/ca_bundle_2019.crt'
            ]);
            if ($result->isOk()) {
                return $result->getDescription();
            }
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            log_message('error', $e);
        }

        return false;
    }

    public function unsetWebHook()
    {
        try {
            $result = $this->telegram->deleteWebhook();
            if ($result->isOk()) {
                return $result->getDescription();
            }
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            log_message('error', $e);
        }

        return false;
    }

    public function hook()
    {
        $commandPath = __DIR__ . '/../commands/';
        try {
            $mysqlCredentials = [
                'host'     => 'localhost',
                'user'     => 'helpdesk_macca',
                'password' => 'helpdesk_macca01',
                'database' => 'uf_helpdesk_macca2',
            ];
            
            $this->telegram->enableMySql($mysqlCredentials, 'uf_telegram_');
            $this->telegram->addCommandsPath($commandPath);
            $this->telegram->handle();
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            log_message('ERROR', $e->getMessage());
        }
    }

    public function sendMessage($userId, $message)
    {
        ci()->load->model('users/user_model');
        $user = ci()->user_model->fields('id,telegram_user')->get(['id' => $userId]);
        if ($user) {
            ci()->db->select('id,username,chat_id');
            ci()->db->join('telegram_user_chat', sprintf('%s.id=%s.user_id', 'telegram_user', 'telegram_user_chat'), 'LEFT');
            $query = ci()->db->get('telegram_user');
            if ($query->num_rows() > 0) {
                $row = $query->row();
                \Longman\TelegramBot\Request::sendMessage([
                    'chat_id' => $row->chat_id,
                    'text' => $message
                ]);
            }
        }
    }
}