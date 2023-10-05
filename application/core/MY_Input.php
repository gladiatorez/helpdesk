<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Input extends CI_Input 
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function is_ajax_post()
	{
		return ($this->is_ajax_request() && $_SERVER['REQUEST_METHOD'] === 'POST');
	}
}