<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['dashboard/backend/api/widget'] = 'Backend_dashboard_api/widget';
$route['dashboard/backend/api/ticket_monthly'] = 'Backend_dashboard_api/ticket_monthly';
$route['dashboard/backend/api/ticket_sbu'] = 'Backend_dashboard_api/ticket_sbu';
$route['dashboard/backend/api/ticket_category'] = 'Backend_dashboard_api/ticket_category';
$route['dashboard/backend/api/ticket_staff'] = 'Backend_dashboard_api/ticket_staff';
$route['dashboard/backend/api(/:any)']            = 'Backend_dashboard_api$1';
