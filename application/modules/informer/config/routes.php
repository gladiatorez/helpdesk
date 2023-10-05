<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['informer/backend/api/form-options'] = 'backend_informer_api/form_options';
$route['informer/backend/api(/:any)'] 			= 'backend_informer_api$1';
$route['informer/backend/api'] 							= 'backend_informer_api/index';