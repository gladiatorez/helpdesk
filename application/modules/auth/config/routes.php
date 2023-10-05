<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['auth/api(/:any)'] = 'auth_api$1';
$route['auth/api'] 	= 'auth_api/index';