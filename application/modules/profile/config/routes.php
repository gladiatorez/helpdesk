<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['profile/api/personalinfo(/:any)']	= 'personal_info_api$1';
$route['profile/api/personalinfo'] 				= 'personal_info_api/index';

$route['profile/backend/api(/:any)'] 	= 'backend_profile_api$1';
$route['profile/backend/api'] 				= 'backend_profile_api/index';