<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['api/request-ticket/sbu'] = 'tickets/request_ticket/sbu';
$route['api/request-ticket/category'] = 'tickets/request_ticket/category';
$route['api/request-ticket/email'] = 'tickets/request_ticket/email';
$route['api/request-ticket/services'] = 'tickets/request_ticket/services';
$route['api/request-ticket/subcategory'] = 'tickets/request_ticket/categorysub';
$route['api/request-ticket/subjectsuggestion'] = 'tickets/request_ticket/subjectsuggestion';
$route['api/request-ticket/niksuggestion'] = 'tickets/request_ticket/niksuggestion';
$route['api/request-ticket/send'] = 'tickets/request_ticket/send';
$route['request-ticket/(:any)'] = 'tickets/request_ticket/$1';
$route['request-ticket'] = 'tickets/request_ticket';

$route['(login|join|logout)'] = 'auth/$1';
$route['auth/forgot-password'] = 'auth/forgot_password';
$route['auth/reset-password/(:any)'] = 'auth/reset_password/$1';

$route['api/([a-zA-Z0-9_-]+)/(:any)'] = '$1/api/$2';
$route['api/([a-zA-Z0-9_-]+)'] 				= '$1/api';

$route['account/(:any)'] 	= 'profile/$1';
$route['account'] 				= 'profile';

$route['acp/auth'] = 'auth/auth_backend';
$route['acp/auth/(:any)'] = 'auth/auth_backend/$1';

// $route['acp/api/addons/([a-zA-Z0-9_-]+)'] = 'addons/backend_addons_api/$1';

$route['acp/api/([a-zA-Z0-9_-]+)/(:any)'] = '$1/backend/api/$2';
$route['acp/api/([a-zA-Z0-9_-]+)'] 				= '$1/backend/api';

$route['acp/reports/(:any)'] = 'reports/backend_reports/$1';
$route['acp/recomendations/(:any)'] = 'recomendations/backend_recomendations/$1';
$route['acp'] = 'acp_controller';

$route['api_ict_dash'] = 'api_ict_dashboard';