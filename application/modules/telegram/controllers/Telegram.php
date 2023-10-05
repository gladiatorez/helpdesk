<?php

/**
 * Class Telegram
 *
 * @property The_telebot $the_telebot
 */
class Telegram extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(false);

        $this->template->set_layout(false);
        $this->load->library('telegram/the_telebot');
    }

    public function hook()
    {
        $this->the_telebot->hook();
    }

    public function set_webhook()
    {
        $this->the_telebot->setWebHook();
    }

    public function unset_webhook()
    {
        $this->the_telebot->unsetWebHook();
    }

    public function updates()
    {
        $this->the_telebot->getUpdates();
    }

    // only for test send message
    public function send_message()
    {
        $this->the_telebot->sendMessage('1', 'Your utf8 text 😜 ...');
    }
}