<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['settings/api(/:any)']   = 'settings_api$1';
$route['settings/api'] 		    = 'settings_api/index';
