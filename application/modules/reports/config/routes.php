<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['reports/backend/api/general(/:any)']    = 'general/backend_reports_general_api$1';
$route['reports/backend/api/general']           = 'general/backend_reports_general_api/index';

$route['reports/backend/api/by_widget(/:any)']    = 'by_widget/backend_reports_widget_api$1';
$route['reports/backend/api/by_widget']           = 'by_widget/backend_reports_widget_api/index';