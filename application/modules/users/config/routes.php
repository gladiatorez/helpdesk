<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['users/backend/api/group(/:any)'] 	= 'group/backend_group_api$1';
$route['users/backend/api/group'] 				= 'group/backend_group_api/index';

$route['users/backend/api/user/get-groups'] 				= 'user/backend_user_api/get_groups';
$route['users/backend/api/user/get-branch-office'] 	= 'user/backend_user_api/get_branch';
$route['users/backend/api/user(/:any)'] 						= 'user/backend_user_api$1';
$route['users/backend/api/user'] 										= 'user/backend_user_api/index';