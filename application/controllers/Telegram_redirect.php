<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Telegram_redirect
 *
 * @property The_auth_backend $the_auth_backend
 * @property The_auth_frontend $the_auth_frontend
 */
class Telegram_redirect extends MY_Controller
{
	public function index()
	{
        $url = $this->input->get('url');
        $urlStrPos = strpos($url, 'helpdesk.kallagroup.co.id/acp');
        $hasAcp = is_int($urlStrPos);
        if ($hasAcp)
        {
            $this->load->library(['auth/the_auth_backend']);

            $userBackend = $this->the_auth_backend->getUserLogin();
            if ($userBackend) {
                if ($url) {
                    $url = str_replace('???', '#', $url);
                    header('Location: https://' . $url);
                }
            }
            else {
                $this->load->view('telegram_redirect');
            }
        }
        else {
            $this->load->library(['auth/the_auth_frontend']);
            $user = $this->the_auth_frontend->getUserLogin();
            if ($user) {
                $url = str_replace('???', '#', $url);
                header('Location: https://' . $url);
            }
            else {
                $this->load->view('telegram_redirect_frontend');
            }
        }
	}
}

