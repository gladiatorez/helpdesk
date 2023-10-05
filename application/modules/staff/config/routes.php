<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['staff/backend/api/pic_level(/:any)'] 		= 'pic_level/backend_pic_level_api$1';
$route['staff/backend/api/pic_level'] 					= 'pic_level/backend_pic_level_api/index';

$route['staff/backend/api/staff/form-options'] = 'staff/backend_staff_api/form_options';
$route['staff/backend/api/staff(/:any)'] = 'staff/backend_staff_api$1';
$route['staff/backend/api/staff'] 			 = 'staff/backend_staff_api/index';