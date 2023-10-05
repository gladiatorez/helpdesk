<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['references/backend/api/reason(/:any)'] 	= 'reason/backend_reason_api$1';
$route['references/backend/api/reason'] 				= 'reason/backend_reason_api/index';

$route['references/backend/api/priority(/:any)'] 	= 'priority/backend_priority_api$1';
$route['references/backend/api/priority'] 				= 'priority/backend_priority_api/index';

$route['references/backend/api/company(/:any)'] = 'company/backend_company_api$1';
$route['references/backend/api/company'] 				= 'company/backend_company_api/index';

$route['references/backend/api/department(/:any)'] 	= 'department/backend_department_api$1';
$route['references/backend/api/department'] 				= 'department/backend_department_api/index';

$route['references/backend/api/keyword(/:any)'] = 'keyword/backend_keyword_api$1';
$route['references/backend/api/keyword'] 				= 'keyword/backend_keyword_api/index';

$route['references/backend/api/category/form-options'] 	= 'category/backend_category_api/form_options';
$route['references/backend/api/category/sub_category'] 	= 'category/backend_category_api/sub_category';
$route['references/backend/api/category/search-staff'] 	= 'category/backend_category_api/search_staff';
$route['references/backend/api/category(/:any)'] 				= 'category/backend_category_api$1';
$route['references/backend/api/category'] 							= 'category/backend_category_api/index';


$route['references/backend/api/email_list(/:any)'] 	= 'email_list/backend_email_list_api$1';
$route['references/backend/api/email_list'] 				= 'email_list/backend_email_list_api/index';