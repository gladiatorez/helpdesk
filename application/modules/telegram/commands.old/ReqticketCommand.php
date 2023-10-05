<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Request;


class ReqticketCommand extends UserCommand
{
    protected $name = 'reqticket';
    protected $description = 'Lakukan permintaan pembuatan tiket';
    protected $usage = '/reqticket';
    protected $version = '1.1.0';
    protected $need_mysql = true;
    protected $private_only = true;

    protected $conversation;

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
            case 0: // step subject
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Enter subject of ticket:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }
                $notes['subject'] = $text;
                $text = '';

            case 1: // step choose category
                $categories = $this->getCategories();
                if ($text === '' || !in_array($text, $categories, true)) {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    try {
                        $reflect = new \ReflectionClass(Keyboard::class);
                        $keyboard = $reflect->newInstanceArgs($categories);
                        $data['reply_markup'] = $keyboard
                            ->setResizeKeyboard(true)
                            ->setOneTimeKeyboard(true)
                            ->setSelective(true);
                    } catch (\ReflectionException $e) {
                        log_message('error', $e);
                    }

                    $data['text'] = 'Choose category:';
                    if ($text !== '') {
                        $data['text'] = 'Select category, choose a keyboard option:';
                    }

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['category'] = $text;
                $text = '';

            case 2: // step choose sub category
                $categories = $this->getSubCategories($notes['category']);
                if ($text === '' || !in_array($text, $categories, true)) {
                    $notes['state'] = 2;
                    $this->conversation->update();

                    try {
                        $reflect = new \ReflectionClass(Keyboard::class);
                        $keyboard = $reflect->newInstanceArgs($categories);
                        $data['reply_markup'] = $keyboard
                            ->setResizeKeyboard(true)
                            ->setOneTimeKeyboard(true)
                            ->setSelective(true);
                    } catch (\ReflectionException $e) {
                        log_message('error', $e);
                    }

                    $data['text'] = 'Choose sub category:';
                    if ($text !== '') {
                        $data['text'] = 'Select sub category, choose a keyboard option:';
                    }

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['subcategory'] = $text;
                $text = '';

            case 3: // subject attachment
                if ($message->getPhoto() === null && !in_array($text, ['I Dont have'], true)) {
                    $notes['state'] = 3;
                    $this->conversation->update();

                    $data['text'] = 'Upload screenshot:';
                    $data['reply_markup'] = (new Keyboard(['I Dont have']))
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(true);
                    $result = Request::sendMessage($data);
                    break;
                }
                if ($message->getPhoto()) {
                    $photo = $message->getPhoto()[0];
                    $notes['photo_id'] = $photo->getFileId();
                }
                else if (in_array($text, ['I Dont have'], true)) {
                    $notes['photo_id'] = '';
                }
                $text = '';

            case 4: // step description
                if ($text === '') {
                    $notes['state'] = 4;
                    $this->conversation->update();

                    $data['text'] = 'Description of your problem:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }
                $notes['descr'] = $text;

            case 5:
                $this->conversation->update();

                $out_text = '/reqticket result:' . PHP_EOL;
                unset($notes['state']);

                $out_text .= PHP_EOL . '*Subject:* '. $notes['subject'];
                $out_text .= PHP_EOL . '*Description:* '. $notes['descr'];
                $out_text .= PHP_EOL . '*Category:* '. $notes['category'];
                $out_text .= PHP_EOL . '*Sub category:* '. $notes['subcategory'];

                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                $data['parse_mode'] = 'MARKDOWN';

                if (empty($notes['photo_id'])) {
                    $out_text .= PHP_EOL . '*Screenshot:* I Dont have';
                    $data['text'] = $out_text;

                    $result = Request::sendMessage($data);
                } else {
                    $data['photo'] = $notes['photo_id'];
                    $data['caption']      = $out_text;

                    $result = Request::sendPhoto($data);
                }

                $this->conversation->stop();
                break;
        }

        return $result;
    }

    protected function getCategories()
    {
        $ci = get_instance();
        $ci->load->model('references/category_model');
        $categories = $ci->category_model->order_by('name')->get_all(['active' => 'A', 'parent_id' => '0']);

        $data = [];
        foreach ($categories as $category) {
            $data[] = $category->name;
        }

        return $data;
    }

    protected function getSubCategories($parentName)
    {
        if (!empty($parentName)) 
        {
            $ci = get_instance();
            $ci->load->model('references/category_model');
            $parent = $ci->category_model->get(['name' => $parentName]);
            if ($parent) {
                $subs = $ci->category_model->order_by('name')->get_all(['parent_id' => $parent->id, 'active' => 'A']);

                $data = [];
                foreach ($subs as $category) {
                    $data[] = $category->name;
                }

                return $data;
            }
        }

        return [];
    }
}
