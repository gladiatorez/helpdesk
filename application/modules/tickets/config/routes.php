<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['tickets/backend/api/company-list'] 	= 'backend_tickets_api/company_list';
$route['tickets/backend/api/department-list'] 	= 'backend_tickets_api/department_list';
$route['tickets/backend/api/category-list'] 	= 'backend_tickets_api/category_list';
$route['tickets/backend/api/keywords-list'] 	= 'backend_tickets_api/keywords_list';
$route['tickets/backend/api(/:any)'] 				= 'backend_tickets_api$1';
$route['tickets/backend/api'] 							= 'backend_tickets_api/index';