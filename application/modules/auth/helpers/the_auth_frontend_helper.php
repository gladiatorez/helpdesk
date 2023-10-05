<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('frontendIsLoggedIn'))
{
	function frontendIsLoggedIn() 
	{
		return ci()->the_auth_frontend->loggedIn();
	}
}
