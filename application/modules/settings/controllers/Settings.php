<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends Backend_Controller {

	public function index()
	{
	    $this->template->build('settings/setting_index');
	}
}
