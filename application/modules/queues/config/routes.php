<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['queues/backend/api/assignment/company-list']      = 'assignment/backend_queues_assignment_api/company_list';
$route['queues/backend/api/assignment/department-list']   = 'assignment/backend_queues_assignment_api/department_list';
$route['queues/backend/api/assignment/category-list']     = 'assignment/backend_queues_assignment_api/category_list';
$route['queues/backend/api/assignment/keywords-list']     = 'assignment/backend_queues_assignment_api/keywords_list';
$route['queues/backend/api/assignment(/:any)']            = 'assignment/backend_queues_assignment_api$1';
$route['queues/backend/api/assignment']                   = 'assignment/backend_queues_assignment_api/index';

$route['queues/backend/api/task/company-list']      = 'task/backend_queues_task_api/company_list';
$route['queues/backend/api/task/department-list']   = 'task/backend_queues_task_api/department_list';
$route['queues/backend/api/task/category-list']     = 'task/backend_queues_task_api/category_list';
$route['queues/backend/api/task/keywords-list']     = 'task/backend_queues_task_api/keywords_list';
$route['queues/backend/api/task(/:any)']            = 'task/backend_queues_task_api$1';
$route['queues/backend/api/task']                   = 'task/backend_queues_task_api/index';
$route['queues/backend/api/task/services']          = 'tickets/request_ticket/services';

$route['queues/backend/api/personil(/:any)']            = 'personil/backend_queues_personil_api$1';
$route['queues/backend/api/personil']                   = 'personil/backend_queues_personil_api/index';