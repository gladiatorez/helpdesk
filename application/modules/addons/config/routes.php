<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['addons/backend/api(/:any)'] = 'backend_addons_api$1';
$route['addons/backend/api'] 				= 'backend_addons_api/index';

$route['addons/api(/:any)'] = 'addons_api$1';
$route['addons/api'] 				= 'addons_api/index';