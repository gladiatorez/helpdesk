<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Backend_Controller {

    public function index()
    {
        $this->template->build('dashboard/welcome_message');
    }
}
