<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['faq/backend/api/form-options'] = 'backend_faq_api/form_options';
$route['faq/backend/api(/:any)'] 	= 'backend_faq_api$1';
$route['faq/backend/api'] 				= 'backend_faq_api/index';

$route['faq/api/rate'] = 'faq/rate';