<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['search/api(/:any)']	= 'search_api$1';
$route['search/api'] 				= 'search_api/index';